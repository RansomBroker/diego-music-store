<?php

namespace App\Livewire;

use App\Actions\SalesDashboard\GetSalesEmployeeDashboardData;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SalesEmployeeDashboardWidgets extends Component
{
    public ?int $selectedEmployeeId = null;

    public function mount(): void
    {
        $user = Auth::user();
        $isOwner = $user && $user->hasRole(['owner', 'admin', 'super_admin', 'Owner', 'Admin', 'Super Admin']);

        if ($isOwner) {
            $firstEmp = Employee::where('is_active', true)
                ->whereDoesntHave('user.roles', fn($q) => $q->whereIn('name', ['owner', 'Owner']))
                ->orderBy('name', 'asc')
                ->first();
            $this->selectedEmployeeId = $firstEmp?->id;
        } else {
            $this->selectedEmployeeId = $user?->employee?->id;
        }
    }

    public function render(GetSalesEmployeeDashboardData $action)
    {
        $currentUser = Auth::user();
        $isOwner = $currentUser && $currentUser->hasRole(['owner', 'admin', 'super_admin', 'Owner', 'Admin', 'Super Admin']);

        // Daftar karyawan untuk dipilih oleh Owner
        $employeesList = $isOwner
            ? Employee::with('user')
                ->where('is_active', true)
                ->whereDoesntHave('user.roles', fn($q) => $q->whereIn('name', ['owner', 'Owner']))
                ->orderBy('name', 'asc')
                ->get()
            : collect();

        // Ambil data karyawan yang sedang dipilih
        $targetEmployee = $this->selectedEmployeeId ? Employee::with('user')->find($this->selectedEmployeeId) : null;
        $targetUser = $targetEmployee?->user;

        $dashboardData = $action->execute($targetUser, $this->selectedEmployeeId);

        return view('livewire.sales-employee-dashboard-widgets', array_merge($dashboardData, [
            'currentUser'        => $currentUser,
            'user'               => $targetUser ?: $currentUser,
            'isOwner'            => $isOwner,
            'employeesList'      => $employeesList,
            'selectedEmployee'   => $targetEmployee,
            'selectedEmployeeId' => $this->selectedEmployeeId,
        ]));
    }
}
