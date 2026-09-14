<?php

namespace App\Livewire;

use App\Actions\OwnerDashboard\GetOwnerDashboardData;
use App\Models\Branch;
use App\Models\Product;
use App\Models\SaleCategory;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OwnerDashboardWidgets extends Component
{
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $branchId = '';
    public string $productCategory = '';
    public string $saleCategory = '';

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo   = now()->format('Y-m-d');
        
        $sessionBranch = session('pos_active_branch_id');
        if ($sessionBranch) {
            $this->branchId = (string) $sessionBranch;
        }
    }

    public function resetFilters()
    {
        $this->dateFrom        = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo          = now()->format('Y-m-d');
        $this->branchId        = '';
        $this->productCategory = '';
        $this->saleCategory    = '';
    }

    public function render(GetOwnerDashboardData $action)
    {
        $user = Auth::user();
        $isOwner = $user && $user->hasRole(['owner', 'admin', 'super_admin', 'Owner', 'Admin', 'Super Admin']);

        if (!$isOwner) {
            return view('livewire.owner-dashboard-widgets', [
                'isOwner' => false,
            ]);
        }

        $filters = [
            'dateFrom'        => $this->dateFrom,
            'dateTo'          => $this->dateTo,
            'branchId'        => $this->branchId !== '' ? (int) $this->branchId : null,
            'productCategory' => $this->productCategory,
            'saleCategory'    => $this->saleCategory,
        ];

        $dashboardData = $action->execute($filters);

        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $productCategories = Product::select('category')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category');

        $saleCategories = class_exists(SaleCategory::class)
            ? SaleCategory::pluck('name')
            : collect(['Retail', 'Grosir', 'Pro Project', 'Online']);

        return view('livewire.owner-dashboard-widgets', array_merge($dashboardData, [
            'isOwner'           => true,
            'branches'          => $branches,
            'productCategories' => $productCategories,
            'saleCategories'    => $saleCategories,
        ]));
    }
}
