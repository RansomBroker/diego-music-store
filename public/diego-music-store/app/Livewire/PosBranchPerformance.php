<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Branch;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use App\Models\ProductBranchStock;
use App\Models\CashTransaction;
use App\Helpers\BranchHelper;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PosBranchPerformance extends Component
{
    public $selectedBranchId = 'all';
    public string $dateFrom = '';
    public string $dateTo = '';

    public function mount()
    {
        $this->dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = Carbon::now()->format('Y-m-d');
        $this->selectedBranchId = 'all';
    }

    public function setQuickDateRange(string $preset)
    {
        switch ($preset) {
            case 'today':
                $this->dateFrom = Carbon::today()->format('Y-m-d');
                $this->dateTo = Carbon::today()->format('Y-m-d');
                break;
            case 'this_month':
                $this->dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
                $this->dateTo = Carbon::now()->endOfMonth()->format('Y-m-d');
                break;
            case 'this_year':
                $this->dateFrom = Carbon::now()->startOfYear()->format('Y-m-d');
                $this->dateTo = Carbon::now()->endOfYear()->format('Y-m-d');
                break;
        }
    }

    public function render()
    {
        $user = Auth::user();
        $allowedBranches = BranchHelper::getAllowedBranchesQuery()->get();

        $targetBranchId = ($this->selectedBranchId && $this->selectedBranchId !== 'all')
            ? (int) $this->selectedBranchId
            : null;

        $currentBranch = $targetBranchId ? Branch::find($targetBranchId) : null;
        $selectedLogoUrl = ($currentBranch && !empty($currentBranch->logo_path) && trim($currentBranch->logo_path) !== '')
            ? Storage::url($currentBranch->logo_path)
            : null;

        // Query sales for selected branch or consolidated all branches
        $salesQuery = Sale::where('status', 'completed')
            ->when($targetBranchId, fn($q) => $q->where('branch_id', $targetBranchId))
            ->when($this->dateFrom, fn($q) => $q->whereDate('invoice_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('invoice_date', '<=', $this->dateTo));

        $totalRevenue = (float) $salesQuery->sum('grand_total');
        $totalSalesCount = $salesQuery->count();

        // Calculate COGS (HPP) for sales
        $saleIds = $salesQuery->pluck('id');
        $totalCogs = count($saleIds) > 0 ? (float) SaleItem::whereIn('sale_id', $saleIds)
            ->join('product_variants', 'sale_items.product_variant_id', '=', 'product_variants.id')
            ->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(product_variants.cost_price, product_variants.hpp, 0) * sale_items.quantity')) : 0;
        $grossProfit = $totalRevenue - $totalCogs;

        // Calculate Branch Operating Expenses (Kas Keluar)
        $expenseQuery = CashTransaction::where('type', 'out')
            ->where('status', 'posted')
            ->when($targetBranchId, fn($q) => $q->where('branch_id', $targetBranchId))
            ->when($this->dateFrom, fn($q) => $q->whereDate('transaction_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('transaction_date', '<=', $this->dateTo));
        
        $totalOperationalExpenses = (float) $expenseQuery->sum('amount');
        $netOperatingIncome = $grossProfit - $totalOperationalExpenses;

        // Stock Valuation per branch
        $stockQuery = ProductBranchStock::with('productVariant')
            ->when($targetBranchId, fn($q) => $q->where('branch_id', $targetBranchId));

        $totalStockItems = (int) $stockQuery->sum('stock');
        $totalStockValue = 0;
        foreach ($stockQuery->get() as $pbs) {
            $cost = $pbs->productVariant?->cost_price ?: ($pbs->productVariant?->hpp ?: 0);
            $totalStockValue += ($pbs->stock * $cost);
        }

        // Customers & AR Piutang
        $totalCustomers = Customer::count();
        $arSales = Sale::where('status', 'completed')
            ->where(function($q) {
                $q->where('payment_method', 'like', '%piutang%')
                  ->orWhere('payment_method', 'like', '%credit%')
                  ->orWhere('payment_method', 'like', '%kredit%');
            })
            ->when($targetBranchId, fn($q) => $q->where('branch_id', $targetBranchId))
            ->get();
        
        $totalArUnpaid = 0;
        foreach ($arSales as $s) {
            $totalArUnpaid += $s->getPiutangAmount();
        }

        // Multi-Branch Comparison Table Data (for Owner / Admin view)
        $branchComparison = [];
        foreach ($allowedBranches as $b) {
            $bSales = Sale::where('status', 'completed')
                ->where('branch_id', $b->id)
                ->when($this->dateFrom, fn($q) => $q->whereDate('invoice_date', '>=', $this->dateFrom))
                ->when($this->dateTo, fn($q) => $q->whereDate('invoice_date', '<=', $this->dateTo));
            
            $bRev = (float) $bSales->sum('grand_total');
            $bSaleIds = $bSales->pluck('id');
            $bCogs = count($bSaleIds) > 0 ? (float) SaleItem::whereIn('sale_id', $bSaleIds)
                ->join('product_variants', 'sale_items.product_variant_id', '=', 'product_variants.id')
                ->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(product_variants.cost_price, product_variants.hpp, 0) * sale_items.quantity')) : 0;
            $bGross = $bRev - $bCogs;

            $bExp = (float) CashTransaction::where('type', 'out')
                ->where('status', 'posted')
                ->where('branch_id', $b->id)
                ->when($this->dateFrom, fn($q) => $q->whereDate('transaction_date', '>=', $this->dateFrom))
                ->when($this->dateTo, fn($q) => $q->whereDate('transaction_date', '<=', $this->dateTo))
                ->sum('amount');
            $bNet = $bGross - $bExp;

            // Stock valuation
            $bStocks = ProductBranchStock::with('productVariant')->where('branch_id', $b->id)->get();
            $bStockCount = $bStocks->sum('stock');
            $bStockVal = 0;
            foreach ($bStocks as $pbs) {
                $bStockVal += ($pbs->stock * ($pbs->productVariant?->cost_price ?: ($pbs->productVariant?->hpp ?: 0)));
            }

            // AR Piutang
            $bArSales = Sale::where('status', 'completed')
                ->where('branch_id', $b->id)
                ->where(function($q) {
                    $q->where('payment_method', 'like', '%piutang%')
                      ->orWhere('payment_method', 'like', '%credit%')
                      ->orWhere('payment_method', 'like', '%kredit%');
                })
                ->get();

            $bAr = 0;
            foreach ($bArSales as $s) {
                $bAr += $s->getPiutangAmount();
            }

            $branchComparison[] = [
                'id'            => $b->id,
                'name'          => $b->name,
                'store_name'    => $b->store_name,
                'city'          => $b->city,
                'revenue'       => $bRev,
                'cogs'          => $bCogs,
                'gross_profit'  => $bGross,
                'expenses'      => $bExp,
                'net_income'    => $bNet,
                'stock_items'   => $bStockCount,
                'stock_value'   => $bStockVal,
                'ar_unpaid'     => $bAr,
            ];
        }

        return view('livewire.pos-branch-performance', [
            'branches'                 => $allowedBranches,
            'currentBranch'            => $currentBranch,
            'selectedLogoUrl'          => $selectedLogoUrl,
            'totalRevenue'             => $totalRevenue,
            'totalSalesCount'          => $totalSalesCount,
            'totalCogs'                => $totalCogs,
            'grossProfit'              => $grossProfit,
            'totalOperationalExpenses' => $totalOperationalExpenses,
            'netOperatingIncome'       => $netOperatingIncome,
            'totalStockItems'          => $totalStockItems,
            'totalStockValue'          => $totalStockValue,
            'totalCustomers'           => $totalCustomers,
            'totalArUnpaid'            => $totalArUnpaid,
            'branchComparison'         => $branchComparison,
        ])->layout('layouts.pos', ['title' => 'Manajemen Performa Cabang — POS']);
    }
}
