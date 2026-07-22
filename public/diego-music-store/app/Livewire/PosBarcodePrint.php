<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ProductVariant;
use App\Models\Branch;
use App\Helpers\BarcodeHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

class PosBarcodePrint extends Component
{
    // Modal & Product Selection Properties
    public bool $showProductModal = false;
    public string $modalSearch = '';
    public string $activeCategory = 'Semua';

    // Queue Search Filter
    public string $queueSearch = '';

    public array $printQueue = []; // array of ['variant_id' => int, 'name' => string, 'sku' => string, 'barcode' => string, 'price' => float, 'qty' => int]

    // Preset & Custom Layout Configuration
    public string $paperLayout = '3col'; // '3col', '2col', '1col', 'custom'

    // Manual Label Dimensions (in mm & px)
    public int $labelWidth = 33;    // mm
    public int $labelHeight = 18;   // mm
    public int $columns = 3;        // 1 to 5
    public int $gapX = 3;           // mm
    public int $gapY = 3;           // mm
    public int $fontSize = 10;      // px
    public int $barcodeHeight = 35; // px

    // Content Display Options
    public bool $showStoreName = true;
    public bool $showProductName = true;
    public bool $showPrice = true;
    public bool $showCode = true;

    public function openProductModal(): void
    {
        $this->modalSearch = '';
        $this->activeCategory = 'Semua';
        $this->showProductModal = true;
    }

    public function closeProductModal(): void
    {
        $this->showProductModal = false;
    }

    public function closeProductSearch(): void
    {
        $this->showProductModal = false;
    }

    public function setCategory(string $category): void
    {
        $this->activeCategory = $category;
    }

    public function addAllProducts(): void
    {
        $variants = ProductVariant::with('product')->get();
        if ($variants->isEmpty()) {
            Notification::make()->title('Perhatian')->body('Tidak ada data produk ditemukan.')->warning()->send();
            return;
        }

        $addedCount = 0;
        foreach ($variants as $variant) {
            if (isset($this->printQueue[$variant->id])) {
                $this->printQueue[$variant->id]['qty']++;
            } else {
                $this->printQueue[$variant->id] = [
                    'variant_id' => $variant->id,
                    'name'       => $variant->product->name . ($variant->name ? ' (' . $variant->name . ')' : ''),
                    'sku'        => $variant->sku ?: '',
                    'barcode'    => $variant->barcode ?: '',
                    'price'      => $variant->price ?: 0,
                    'qty'        => 1,
                ];
            }
            $addedCount++;
        }

        Notification::make()
            ->title('Berhasil Menambahkan Semua Produk')
            ->body("Sebanyak {$addedCount} produk/varian telah dimasukkan ke dalam antrean cetak barcode.")
            ->success()
            ->send();
    }

    public function updatedPaperLayout(string $value): void
    {
        switch ($value) {
            case '3col':
                $this->labelWidth    = 33;
                $this->labelHeight   = 18;
                $this->columns       = 3;
                $this->gapX          = 3;
                $this->gapY          = 3;
                $this->fontSize      = 10;
                $this->barcodeHeight = 35;
                break;
            case '2col':
                $this->labelWidth    = 40;
                $this->labelHeight   = 22;
                $this->columns       = 2;
                $this->gapX          = 4;
                $this->gapY          = 4;
                $this->fontSize      = 11;
                $this->barcodeHeight = 40;
                break;
            case '1col':
                $this->labelWidth    = 50;
                $this->labelHeight   = 30;
                $this->columns       = 1;
                $this->gapX          = 0;
                $this->gapY          = 5;
                $this->fontSize      = 12;
                $this->barcodeHeight = 45;
                break;
            case 'custom':
                // keep current dimensions
                break;
        }
    }

    public function touchCustom(): void
    {
        $this->paperLayout = 'custom';
    }

    public function addVariant(int $variantId): void
    {
        $variant = ProductVariant::with('product')->find($variantId);
        if (!$variant) return;

        if (isset($this->printQueue[$variantId])) {
            $this->printQueue[$variantId]['qty']++;
        } else {
            $this->printQueue[$variantId] = [
                'variant_id' => $variant->id,
                'name'       => $variant->product->name . ($variant->name ? ' (' . $variant->name . ')' : ''),
                'sku'        => $variant->sku ?: '',
                'barcode'    => $variant->barcode ?: '',
                'price'      => $variant->price ?: 0,
                'qty'        => 1,
            ];
        }

        Notification::make()
            ->title('Produk Ditambahkan')
            ->body("{$variant->product->name} berhasil ditambahkan ke antrean.")
            ->success()
            ->send();
    }

    public function updateQty(int $variantId, int $qty): void
    {
        if ($qty <= 0) {
            $this->removeVariant($variantId);
        } else {
            $this->printQueue[$variantId]['qty'] = $qty;
        }
    }

    public function removeVariant(int $variantId): void
    {
        unset($this->printQueue[$variantId]);
    }

    public function clearQueue(): void
    {
        $this->printQueue = [];
        $this->queueSearch = '';
    }

    public function triggerPrint(): void
    {
        if (empty($this->printQueue)) {
            Notification::make()->title('Antrean Cetak Kosong')->body('Pilih setidaknya satu produk untuk dicetak barcode.')->warning()->send();
            return;
        }

        $payload = base64_encode(json_encode([
            'queue'          => array_values($this->printQueue),
            'layout'         => $this->paperLayout,
            'label_width'    => $this->labelWidth,
            'label_height'   => $this->labelHeight,
            'columns'        => $this->columns,
            'gap_x'          => $this->gapX,
            'gap_y'          => $this->gapY,
            'font_size'      => $this->fontSize,
            'barcode_height' => $this->barcodeHeight,
            'show_store'     => $this->showStoreName,
            'show_name'      => $this->showProductName,
            'show_price'     => $this->showPrice,
            'show_code'      => $this->showCode,
        ]));

        $this->js("window.open('/pos/barcode-print/sheet?data={$payload}', '_blank')");
    }

    private function getCategoryOfVariant($variant): string
    {
        if ($variant->product->isService()) {
            return 'Jasa Reparasi';
        }

        $name = strtolower($variant->product->name . ' ' . ($variant->name ?? ''));

        if (str_contains($name, 'gitar') || str_contains($name, 'guitar') || str_contains($name, 'bass')) {
            return 'Gitar & Bass';
        }
        if (str_contains($name, 'keyboard') || str_contains($name, 'piano') || str_contains($name, 'organ')) {
            return 'Keyboard & Piano';
        }
        if (str_contains($name, 'drum') || str_contains($name, 'stick') || str_contains($name, 'perkusi')) {
            return 'Drum & Perkusi';
        }
        if (str_contains($name, 'senar') || str_contains($name, 'kabel') || str_contains($name, 'jack') || str_contains($name, 'aksesoris')) {
            return 'Aksesoris';
        }

        return 'Aksesoris';
    }

    public function render()
    {
        $modalProducts = collect([]);
        if ($this->showProductModal) {
            $query = ProductVariant::with(['product']);

            if (strlen(trim($this->modalSearch)) > 0) {
                $search = trim($this->modalSearch);
                $query->where(function ($q) use ($search) {
                    $q->where('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                        ->orWhereHas('product', fn($pq) => $pq->where('name', 'like', "%{$search}%"));
                });
            }

            $allVariants = $query->take(60)->get();

            $modalProducts = $allVariants->filter(function ($variant) {
                if ($this->activeCategory === 'Semua') {
                    return true;
                }
                return $this->getCategoryOfVariant($variant) === $this->activeCategory;
            });
        }

        // Filter queue items by queueSearch
        $filteredQueue = $this->printQueue;
        if (strlen(trim($this->queueSearch)) > 0) {
            $qs = strtolower(trim($this->queueSearch));
            $filteredQueue = array_filter($this->printQueue, function ($item) use ($qs) {
                return str_contains(strtolower($item['name']), $qs) ||
                       str_contains(strtolower($item['sku'] ?? ''), $qs) ||
                       str_contains(strtolower($item['barcode'] ?? ''), $qs);
            });
        }

        $userBranchId = Auth::user()->branches()->first()?->id;
        $branch = $userBranchId ? Branch::find($userBranchId) : null;
        $selectedLogoUrl = ($branch && !empty($branch->logo_path))
            ? Storage::url($branch->logo_path)
            : null;

        return view('livewire.pos-barcode-print', [
            'modalProducts'    => $modalProducts,
            'filteredQueue'    => $filteredQueue,
            'selectedLogoUrl'  => $selectedLogoUrl,
            'selectedBranchId' => $userBranchId,
        ])->layout('layouts.pos', ['title' => 'Cetak Barcode — POS']);
    }
}
