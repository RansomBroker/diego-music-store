<?php

namespace App\Livewire;

use App\Actions\SalesDashboard\GetSalesEmployeeDashboardData;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SalesEmployeeDashboardWidgets extends Component
{
    public function render(GetSalesEmployeeDashboardData $action)
    {
        $user = Auth::user();
        $dashboardData = $action->execute($user);

        return view('livewire.sales-employee-dashboard-widgets', array_merge($dashboardData, [
            'user' => $user,
        ]));
    }
}
