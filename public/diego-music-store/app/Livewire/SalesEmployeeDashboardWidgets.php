<?php

namespace App\Livewire;

use App\Actions\SalesDashboard\GetSalesEmployeeDashboardData;
use App\Helpers\PermissionHelper;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SalesEmployeeDashboardWidgets extends Component
{
    public ?int $selectedEmployeeId = null;

    public function mount(): void
    {
        $user = Auth::user();
        $isOwner = PermissionHelper::isOwner($user);

        if (! $isOwner) {
            $this->selectedEmployeeId = $user?->employee?->id
                ?? ($user ? Employee::where('user_id', $user->id)->value('id') : null);

            return;
        }

        $activeBranchId = \App\Helpers\BranchHelper::getActiveBranchId();
        $firstEmp = Employee::where('is_active', true)
            ->when($activeBranchId, fn($q) => $q->where('branch_id', $activeBranchId))
            ->withActiveUser(excludeOwners: true)
            ->orderBy('name', 'asc')
            ->first();
        $this->selectedEmployeeId = $firstEmp?->id;
    }

    public function render(GetSalesEmployeeDashboardData $action)
    {
        $currentUser = Auth::user();
        $isOwner = PermissionHelper::isOwner($currentUser);

        if (! $isOwner) {
            $ownEmployee = $currentUser?->employee
                ?: ($currentUser ? Employee::with('user')->where('user_id', $currentUser->id)->first() : null);

            // Kunci ketat ke data karyawan yang sedang login agar tidak bisa melihat performa staf lain
            $this->selectedEmployeeId = $ownEmployee?->id;

            $dashboardData = $action->execute($currentUser, $ownEmployee?->id);

            return view('livewire.sales-employee-dashboard-widgets', array_merge($dashboardData, [
                'currentUser'        => $currentUser,
                'user'               => $currentUser,
                'isOwner'            => false,
                'employeesList'      => collect(),
                'selectedEmployee'   => $ownEmployee,
                'selectedEmployeeId' => $ownEmployee?->id,
            ]));
        }

        $activeBranchId = \App\Helpers\BranchHelper::getActiveBranchId();

        // Daftar karyawan aktif (hanya dengan user aktif & non-owner) untuk dipilih oleh Owner
        $employeesList = Employee::with('user')
            ->where('is_active', true)
            ->when($activeBranchId, fn($q) => $q->where('branch_id', $activeBranchId))
            ->withActiveUser(excludeOwners: true)
            ->orderBy('name', 'asc')
            ->get();

        if ($this->selectedEmployeeId && ! $employeesList->contains('id', $this->selectedEmployeeId)) {
            $this->selectedEmployeeId = $employeesList->first()?->id;
        }

        // Ambil data karyawan yang sedang dipilih
        $targetEmployee = $this->selectedEmployeeId ? Employee::with('user')->find($this->selectedEmployeeId) : null;
        $targetUser = $targetEmployee?->user;

        $dashboardData = $action->execute($targetUser, $this->selectedEmployeeId);

        return view('livewire.sales-employee-dashboard-widgets', array_merge($dashboardData, [
            'currentUser'        => $currentUser,
            'user'               => $targetUser ?: $currentUser,
            'isOwner'            => true,
            'employeesList'      => $employeesList,
            'selectedEmployee'   => $targetEmployee,
            'selectedEmployeeId' => $this->selectedEmployeeId,
        ]));
    }
}
