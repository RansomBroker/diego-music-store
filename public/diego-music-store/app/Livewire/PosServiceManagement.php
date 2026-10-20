<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ServiceOrder;
use App\Models\Branch;
use App\Models\User;
use App\Actions\Service\UpdateServiceOrderStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PosServiceManagement extends Component
{
    use WithPagination;

    public function boot(): void
    {
        // Sales staff have read-only access to the product catalog only.
        abort_if(auth()->user()?->hasRole(['sales', 'Sales']), 403);
    }

    private function resolveAuthorizedBranchId(): int
    {
        $allowedBranchIds = \App\Helpers\BranchHelper::getAllowedBranchesQuery()
            ->pluck('branches.id')->map(fn ($id) => (int) $id)->all();
        $requestedBranchId = (int) ($this->selectedBranchId ?? 0);
        $branchId = $requestedBranchId && in_array($requestedBranchId, $allowedBranchIds, true)
            ? $requestedBranchId
            : \App\Helpers\BranchHelper::getActiveBranchId();
        abort_unless(in_array($branchId, $allowedBranchIds, true), 403);
        $this->selectedBranchId = $branchId;
        return $branchId;
    }

    public ?int $selectedBranchId = null;
    public ?string $selectedStatus = null;
    public ?int $selectedTechnicianId = null;
    public ?string $search = '';
    public int $perPage = 25;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedStatus(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedBranchId(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedTechnicianId(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage($value): void
    {
        $this->perPage = (int) $value;
        $this->resetPage();
    }

    // Modal state for managing service ticket
    public bool $showEditModal = false;
    public ?int $editingOrderId = null;
    public string $editStatus = 'received';
    public ?int $editTechnicianId = null;
    public ?string $editSerialNumber = '';
    public ?string $editComplaint = '';
    public ?string $editNotes = '';
    public array $editAdditionalCharges = [];

    public function resetFilters()
    {
        $this->selectedBranchId = null;
        $this->selectedStatus = null;
        $this->selectedTechnicianId = null;
        $this->search = '';
        $this->resetPage();
    }

    public function openEditModal(int $orderId)
    {
        $order = ServiceOrder::query()->where('branch_id', $this->resolveAuthorizedBranchId())->findOrFail($orderId);
        $this->editingOrderId = $order->id;
        $this->editStatus = $order->status;
        $this->editTechnicianId = $order->technician_id;
        $this->editSerialNumber = $order->serial_number ?: '';
        $this->editComplaint = $order->complaint ?: '';
        $this->editNotes = $order->notes ?: '';
        $this->editAdditionalCharges = $order->additional_charges ?: [];
        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editingOrderId = null;
        $this->editAdditionalCharges = [];
    }

    public function addAdditionalChargeRow()
    {
        $this->editAdditionalCharges[] = [
            'name'   => '',
            'amount' => 0,
        ];
    }

    public function removeAdditionalChargeRow(int $index)
    {
        unset($this->editAdditionalCharges[$index]);
        $this->editAdditionalCharges = array_values($this->editAdditionalCharges);
    }

    public function saveServiceOrder()
    {
        if (!$this->editingOrderId) {
            return;
        }

        $order = ServiceOrder::query()->where('branch_id', $this->resolveAuthorizedBranchId())->findOrFail($this->editingOrderId);

        // Sanitize charges
        $cleanCharges = [];
        foreach ($this->editAdditionalCharges as $chg) {
            if (!empty($chg['name']) && floatval($chg['amount']) > 0) {
                $cleanCharges[] = [
                    'name'   => trim($chg['name']),
                    'amount' => floatval($chg['amount']),
                ];
            }
        }

        UpdateServiceOrderStatus::execute($order, [
            'status'             => $this->editStatus,
            'technician_id'      => $this->editTechnicianId,
            'serial_number'      => $this->editSerialNumber,
            'complaint'          => $this->editComplaint,
            'notes'              => $this->editNotes,
            'additional_charges' => $cleanCharges,
        ]);

        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => 'Status Tiket Service #' . $order->ticket_code . ' berhasil diperbarui.',
        ]);
        $this->dispatch('toast', type: 'success', message: 'Status Tiket Service #' . $order->ticket_code . ' berhasil diperbarui.');

        $this->closeEditModal();
    }

    public function render()
    {
        $branches = \App\Helpers\BranchHelper::getAllowedBranchesQuery()->get();
        $activeBranchId = $this->resolveAuthorizedBranchId();
        $currentBranch = Branch::findOrFail($activeBranchId);

        $selectedLogoUrl = !empty($currentBranch?->logo_path) ? Storage::url($currentBranch->logo_path) : null;

        // Fetch technicians
        $technicians = User::whereHas('roles', fn($q) => $q->whereIn('name', ['technician', 'Technician', 'sales', 'Sales', 'cashier', 'Cashier', 'owner', 'Owner', 'super_admin', 'Super Admin']))
            ->whereHas('employee', fn ($q) => $q->where('branch_id', $activeBranchId))
            ->orderBy('name')
            ->get();

        // Build query
        $query = ServiceOrder::with(['branch', 'customer', 'technician', 'sale'])
            ->latest('id');

        if ($activeBranchId) {
            $query->where('branch_id', $activeBranchId);
        }

        if ($this->selectedStatus) {
            $query->where('status', $this->selectedStatus);
        }

        if ($this->selectedTechnicianId) {
            $query->where('technician_id', $this->selectedTechnicianId);
        }

        if (!empty($this->search)) {
            $s = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('ticket_code', 'like', $s)
                  ->orWhere('customer_name', 'like', $s)
                  ->orWhere('device_name', 'like', $s)
                  ->orWhere('serial_number', 'like', $s);
            });
        }

        $orders = $this->perPage > 0
            ? $query->paginate($this->perPage)
            : $query->paginate(max(1, $query->count()));

        // Status Summary counts
        $statusCounts = [
            'all'           => ServiceOrder::where('branch_id', $activeBranchId)->count(),
            'received'      => ServiceOrder::where('branch_id', $activeBranchId)->where('status', 'received')->count(),
            'in_progress'   => ServiceOrder::where('branch_id', $activeBranchId)->whereIn('status', ['diagnosing', 'in_progress', 'waiting_parts'])->count(),
            'completed'     => ServiceOrder::where('branch_id', $activeBranchId)->where('status', 'completed')->count(),
            'picked_up'     => ServiceOrder::where('branch_id', $activeBranchId)->where('status', 'picked_up')->count(),
        ];

        return view('livewire.pos-service-management', [
            'branches'        => $branches,
            'currentBranch'   => $currentBranch,
            'selectedLogoUrl' => $selectedLogoUrl,
            'technicians'     => $technicians,
            'orders'          => $orders,
            'statusCounts'    => $statusCounts,
        ])->layout('layouts.pos', ['title' => 'Manajemen Barang Service — POS']);
    }
}
