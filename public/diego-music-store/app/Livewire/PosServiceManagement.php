<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ServiceOrder;
use App\Models\Branch;
use App\Models\User;
use App\Actions\Service\UpdateServiceOrderStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PosServiceManagement extends Component
{
    public ?int $selectedBranchId = null;
    public ?string $selectedStatus = null;
    public ?int $selectedTechnicianId = null;
    public ?string $search = '';

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
    }

    public function openEditModal(int $orderId)
    {
        $order = ServiceOrder::findOrFail($orderId);
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

        $order = ServiceOrder::findOrFail($this->editingOrderId);

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

        $this->closeEditModal();
    }

    public function render()
    {
        $branches = \App\Helpers\BranchHelper::getAllowedBranchesQuery()->get();
        $activeBranchId = $this->selectedBranchId ?: \App\Helpers\BranchHelper::getActiveBranchId();
        $currentBranch = Branch::find($activeBranchId);

        $selectedLogoUrl = !empty($currentBranch?->logo_path) ? Storage::url($currentBranch->logo_path) : null;

        // Fetch technicians
        $technicians = User::whereHas('roles', fn($q) => $q->whereIn('name', ['technician', 'sales', 'cashier', 'owner', 'admin']))
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

        $orders = $query->paginate(25);

        // Status Summary counts
        $statusCounts = [
            'all'           => ServiceOrder::count(),
            'received'      => ServiceOrder::where('status', 'received')->count(),
            'in_progress'   => ServiceOrder::whereIn('status', ['diagnosing', 'in_progress', 'waiting_parts'])->count(),
            'completed'     => ServiceOrder::where('status', 'completed')->count(),
            'picked_up'     => ServiceOrder::where('status', 'picked_up')->count(),
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
