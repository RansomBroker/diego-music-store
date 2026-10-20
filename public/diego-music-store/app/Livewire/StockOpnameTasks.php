<?php

namespace App\Livewire;

use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\StockOpname;
use App\Models\StockOpnameAssignment;
use App\Models\StockOpnameItem;
use App\Models\StockOpnamePeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class StockOpnameTasks extends Component
{
    public string $search = '';
    public ?int $activeAssignmentId = null;
    public ?int $selectedVariantId = null;
    public string $selectedPhysicalQty = '0';
    public bool $showCountModal = false;

    public function mount(): void
    {
        abort_unless(auth()->check(), 403);
    }

    public function updatedSearch(): void
    {
        // Search is intentionally server-side and limited to a small result set.
    }

    public function startTask(int $assignmentId): void
    {
        $assignment = $this->findMyAssignment($assignmentId);
        $period = $assignment->period;

        if ($period->status !== StockOpnamePeriod::STATUS_ACTIVE) {
            $this->dispatchToast('warning', 'Periode belum aktif', 'Tugas hanya dapat dikerjakan ketika periode opname aktif.');
            return;
        }

        if (!in_array($assignment->status, [
            StockOpnameAssignment::STATUS_ASSIGNED,
            StockOpnameAssignment::STATUS_IN_PROGRESS,
            StockOpnameAssignment::STATUS_NEEDS_REVISION,
        ], true)) {
            return;
        }

        DB::transaction(function () use ($assignment): void {
            $locked = StockOpnameAssignment::query()
                ->whereKey($assignment->id)
                ->where('assigned_user_id', auth()->id())
                ->lockForUpdate()
                ->firstOrFail();

            if (!$locked->stock_opname_id) {
                $opname = StockOpname::create([
                    'branch_id' => $locked->period->branch_id,
                    'opname_date' => $locked->period->period,
                    'status' => 'draft',
                    'notes' => 'Opname bulanan: ' . $locked->period->name
                        . ' | Lokasi: ' . $locked->preset->name
                        . ' | Assignment #' . $locked->id,
                ]);

                $locked->stock_opname_id = $opname->id;
            }

            if ($locked->status !== StockOpnameAssignment::STATUS_NEEDS_REVISION) {
                $locked->status = StockOpnameAssignment::STATUS_IN_PROGRESS;
            }

            $locked->started_at = $locked->started_at ?: now();
            $locked->save();
        });

        $this->activeAssignmentId = $assignment->id;
        $this->search = '';
        $this->showCountModal = false;
    }

    public function chooseProduct(int $variantId): void
    {
        $assignment = $this->activeAssignment();

        abort_unless($assignment, 404);
        $this->ensureEditable($assignment);

        $variant = $this->searchableVariants($assignment)
            ->whereKey($variantId)
            ->firstOrFail();

        $existingItem = $assignment->stockOpname?->items()
            ->where('product_variant_id', $variant->id)
            ->first();

        $this->selectedVariantId = $variant->id;
        $this->selectedPhysicalQty = (string) ($existingItem?->physical_qty ?? 0);
        $this->showCountModal = true;
        $this->resetValidation();
    }

    public function saveCount(): void
    {
        $assignment = $this->activeAssignment();

        abort_unless($assignment, 404);
        $this->ensureEditable($assignment);

        $validated = $this->validate([
            'selectedVariantId' => ['required', 'integer'],
            'selectedPhysicalQty' => ['required', 'integer', 'min:0', 'max:1000000'],
        ], [
            'selectedPhysicalQty.required' => 'Jumlah fisik wajib diisi.',
            'selectedPhysicalQty.integer' => 'Jumlah fisik harus berupa bilangan bulat.',
            'selectedPhysicalQty.min' => 'Jumlah fisik tidak boleh negatif.',
            'selectedPhysicalQty.max' => 'Jumlah fisik terlalu besar.',
        ]);

        $variant = $this->searchableVariants($assignment)
            ->whereKey((int) $validated['selectedVariantId'])
            ->firstOrFail();

        DB::transaction(function () use ($assignment, $variant, $validated): void {
            $opname = StockOpname::query()
                ->whereKey($assignment->stock_opname_id)
                ->where('branch_id', $assignment->period->branch_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($opname->status !== 'draft') {
                throw ValidationException::withMessages([
                    'selectedPhysicalQty' => 'Dokumen opname sudah dikunci dan tidak dapat diubah.',
                ]);
            }

            $item = StockOpnameItem::query()
                ->where('stock_opname_id', $opname->id)
                ->where('product_variant_id', $variant->id)
                ->first();

            if (!$item) {
                $branchStock = ProductBranchStock::query()
                    ->where('branch_id', $opname->branch_id)
                    ->where('product_variant_id', $variant->id)
                    ->first();

                $costPrice = (int) (($branchStock?->hpp > 0)
                    ? $branchStock->hpp
                    : ($variant->hpp ?: $variant->cost_price ?: 0));

                $item = new StockOpnameItem([
                    'stock_opname_id' => $opname->id,
                    'product_variant_id' => $variant->id,
                    'system_qty' => (int) ($branchStock?->stock ?? 0),
                    'cost_price' => $costPrice,
                ]);
            }

            $physicalQty = (int) $validated['selectedPhysicalQty'];
            $item->physical_qty = $physicalQty;
            $item->difference = $physicalQty - (int) $item->system_qty;
            $item->save();
        });

        $this->showCountModal = false;
        $this->selectedVariantId = null;
        $this->selectedPhysicalQty = '0';
        $this->dispatchToast('success', 'Hitungan tersimpan', 'Jumlah fisik tersimpan untuk lokasi tugas ini.');
    }

    public function submitTask(): void
    {
        $assignment = $this->activeAssignment();

        abort_unless($assignment, 404);
        $this->ensureEditable($assignment);

        if (!$assignment->stockOpname || !$assignment->stockOpname->items()->exists()) {
            throw ValidationException::withMessages([
                'search' => 'Hitung minimal satu produk sebelum mengirim tugas.',
            ]);
        }

        DB::transaction(function () use ($assignment): void {
            $locked = StockOpnameAssignment::query()
                ->whereKey($assignment->id)
                ->where('assigned_user_id', auth()->id())
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureEditable($locked);

            $locked->status = StockOpnameAssignment::STATUS_SUBMITTED;
            $locked->submitted_at = now();
            $locked->save();

            $period = StockOpnamePeriod::query()
                ->whereKey($locked->stock_opname_period_id)
                ->lockForUpdate()
                ->firstOrFail();

            $remaining = $period->assignments()
                ->whereNotIn('status', [
                    StockOpnameAssignment::STATUS_SUBMITTED,
                    StockOpnameAssignment::STATUS_COMPLETED,
                    StockOpnameAssignment::STATUS_CANCELLED,
                ])
                ->exists();

            if (!$remaining && $period->status === StockOpnamePeriod::STATUS_ACTIVE) {
                $period->status = StockOpnamePeriod::STATUS_REVIEW;
                $period->save();
            }
        });

        $this->activeAssignmentId = null;
        $this->showCountModal = false;
        $this->dispatchToast('success', 'Tugas dikirim', 'Hitungan masuk ke tahap review. Stok cabang belum diubah.');
    }

    public function backToTasks(): void
    {
        $this->activeAssignmentId = null;
        $this->showCountModal = false;
        $this->selectedVariantId = null;
        $this->search = '';
        $this->resetValidation();
    }

    private function findMyAssignment(int $assignmentId): StockOpnameAssignment
    {
        $assignment = StockOpnameAssignment::query()
            ->with(['period.branch', 'preset', 'stockOpname.items.productVariant.product'])
            ->whereKey($assignmentId)
            ->where('assigned_user_id', auth()->id())
            ->first();

        abort_unless($assignment, 404);

        return $assignment;
    }

    private function activeAssignment(): ?StockOpnameAssignment
    {
        if (!$this->activeAssignmentId) {
            return null;
        }

        return $this->findMyAssignment($this->activeAssignmentId);
    }

    private function ensureEditable(StockOpnameAssignment $assignment): void
    {
        abort_unless((int) $assignment->assigned_user_id === (int) auth()->id(), 403);
        abort_unless($assignment->period?->status === StockOpnamePeriod::STATUS_ACTIVE, 403);
        abort_unless(in_array($assignment->status, [
            StockOpnameAssignment::STATUS_ASSIGNED,
            StockOpnameAssignment::STATUS_IN_PROGRESS,
            StockOpnameAssignment::STATUS_NEEDS_REVISION,
        ], true), 403);
        abort_unless($assignment->stockOpname && $assignment->stockOpname->status === 'draft', 403);
    }

    private function searchableVariants(StockOpnameAssignment $assignment)
    {
        return ProductVariant::query()
            ->select('product_variants.*')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->with(['product:id,name,category,type', 'branchStocks' => function ($query) use ($assignment) {
                $query->where('branch_id', $assignment->period->branch_id)
                    ->select('id', 'product_variant_id', 'branch_id', 'stock');
            }])
            ->where('products.type', 'physical')
            ->where(function ($query) use ($assignment) {
                $query->where('product_variants.is_active', true)
                    ->orWhereHas('branchStocks', fn ($stock) => $stock
                        ->where('branch_id', $assignment->period->branch_id)
                        ->where('stock', '>', 0));
            })
            ->where(function ($query) use ($assignment) {
                $query->where('products.is_active', true)
                    ->orWhereHas('branchStocks', fn ($stock) => $stock
                        ->where('branch_id', $assignment->period->branch_id)
                        ->where('stock', '>', 0));
            })
            ->when(trim($this->search) !== '', function ($query) {
                $term = '%' . addcslashes(trim($this->search), '%_') . '%';
                $query->where(function ($nested) use ($term) {
                    $nested->where('products.name', 'like', $term)
                        ->orWhere('products.category', 'like', $term)
                        ->orWhere('product_variants.name', 'like', $term)
                        ->orWhere('product_variants.sku', 'like', $term)
                        ->orWhere('product_variants.barcode', 'like', $term);
                });
            })
            ->orderBy('products.name')
            ->orderBy('product_variants.name')
            ->limit(20);
    }

    private function dispatchToast(string $type, string $title, string $body): void
    {
        $this->dispatch('toast', [
            'type' => $type,
            'title' => $title,
            'body' => $body,
        ]);
    }

    public function render()
    {
        $userId = auth()->id();

        $assignments = StockOpnameAssignment::query()
            ->with(['period.branch', 'preset', 'stockOpname.items'])
            ->where('assigned_user_id', $userId)
            ->whereHas('period', fn ($query) => $query->whereIn('status', [
                StockOpnamePeriod::STATUS_ACTIVE,
                StockOpnamePeriod::STATUS_REVIEW,
                StockOpnamePeriod::STATUS_COMPLETED,
            ]))
            ->where('status', '!=', StockOpnameAssignment::STATUS_CANCELLED)
            ->latest('assigned_at')
            ->get();

        $activeAssignment = $this->activeAssignment();
        $products = $activeAssignment
            && $activeAssignment->period->status === StockOpnamePeriod::STATUS_ACTIVE
            && in_array($activeAssignment->status, [
                StockOpnameAssignment::STATUS_ASSIGNED,
                StockOpnameAssignment::STATUS_IN_PROGRESS,
                StockOpnameAssignment::STATUS_NEEDS_REVISION,
            ], true)
            && mb_strlen(trim($this->search)) >= 2
            ? $this->searchableVariants($activeAssignment)->get()
            : collect();

        $selectedVariant = $this->selectedVariantId
            ? ProductVariant::with('product')->find($this->selectedVariantId)
            : null;

        $selectedLogoUrl = null;
        $activeBranchId = session('pos_active_branch_id') ?: auth()->user()?->employee?->branch_id;
        if ($activeBranchId) {
            $branch = \App\Models\Branch::find($activeBranchId);
            $selectedLogoUrl = ($branch && $branch->logo_path) ? \Illuminate\Support\Facades\Storage::url($branch->logo_path) : null;
        }

        return view('livewire.stock-opname-tasks', [
            'assignments' => $assignments,
            'activeAssignment' => $activeAssignment,
            'products' => $products,
            'selectedVariant' => $selectedVariant,
            'selectedLogoUrl' => $selectedLogoUrl,
        ])->layout('layouts.pos', ['title' => 'Tugas Stok Opname']);
    }
}
