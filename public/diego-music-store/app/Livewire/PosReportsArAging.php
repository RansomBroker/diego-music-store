<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Helpers\ReportHelper;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PosReportsArAging extends Component
{
    use WithPagination;

    public ?int $selectedBranchId = null;
    public ?string $search = '';
    public ?int $selectedCustomerId = null;
    public ?string $agingGroupFilter = '';
    public ?string $dateFrom = '';
    public ?string $dateTo = '';
    public int $perPage = 15;

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage($value): void
    {
        $this->perPage = (int) $value;
        $this->resetPage();
    }

    public function updated($propertyName): void
    {
        if (in_array($propertyName, ['selectedBranchId', 'search', 'selectedCustomerId', 'agingGroupFilter', 'dateFrom', 'dateTo', 'perPage'])) {
            $this->resetPage();
        }
    }

    // ── Detail & History Modal State ───────────────────────────────────
    public bool $showDetailModal = false;
    public ?Sale $selectedSale = null;
    public array $settlementHistory = [];
    public int $selectedAgeDays = 0;
    public string $selectedAgingGroup = '';

    public function resetFilters(): void
    {
        $this->reset(['search', 'selectedCustomerId', 'agingGroupFilter', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function showDetails(int $saleId): void
    {
        $sale = Sale::with(['customer', 'branch', 'salesRep', 'items.variant.product'])->find($saleId);
        if (!$sale) {
            return;
        }

        $this->selectedSale = $sale;
        $invDate = \Carbon\Carbon::parse($sale->invoice_date);
        $this->selectedAgeDays = max(0, $invDate->diffInDays(\Carbon\Carbon::today()));
        if ($this->selectedAgeDays <= 30) {
            $this->selectedAgingGroup = '0 - 30 Hari (Lancar)';
        } elseif ($this->selectedAgeDays <= 60) {
            $this->selectedAgingGroup = '31 - 60 Hari';
        } elseif ($this->selectedAgeDays <= 90) {
            $this->selectedAgingGroup = '61 - 90 Hari';
        } else {
            $this->selectedAgingGroup = '> 90 Hari (Menunggak)';
        }

        // Fetch settlement history journal entries for this Sale
        $journalEntries = \App\Models\JournalEntry::with(['items.account', 'user'])
            ->where('reference_type', 'Sales')
            ->where('reference_id', $sale->id)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $piutangAccount = \App\Models\Account::where('code', '1-1200')->first();

        $history = [];
        foreach ($journalEntries as $je) {
            $piutangCredit = $je->items->first(fn($item) => $piutangAccount && $item->account_id === $piutangAccount->id && floatval($item->credit) > 0);
            $kasDebit = $je->items->first(fn($item) => floatval($item->debit) > 0);

            if ($piutangCredit || $kasDebit) {
                $amount = $piutangCredit ? floatval($piutangCredit->credit) : floatval($kasDebit?->debit ?? 0);
                $accountName = $kasDebit?->account?->name ?? 'Kas / Bank';

                $history[] = [
                    'id' => $je->id,
                    'entry_number' => $je->entry_no ?? ('JE-' . str_pad($je->id, 5, '0', STR_PAD_LEFT)),
                    'date' => $je->date ? \Carbon\Carbon::parse($je->date)->format('d/m/Y') : '-',
                    'account_name' => $accountName,
                    'description' => $je->description ?? 'Pelunasan Piutang',
                    'user_name' => $je->user?->name ?? $je->creator?->name ?? 'System/Kasir',
                    'amount' => $amount,
                ];
            }
        }

        $this->settlementHistory = $history;
        $this->showDetailModal = true;
    }

    public function closeDetails(): void
    {
        $this->showDetailModal = false;
        $this->selectedSale = null;
        $this->settlementHistory = [];
    }

    public function render()
    {
        $branches = Branch::all();
        $userBranchId = Auth::user()?->branches()->first()?->id;
        $currentBranch = $this->selectedBranchId
            ? Branch::find($this->selectedBranchId)
            : ($userBranchId ? Branch::find($userBranchId) : Branch::first());

        $selectedLogoUrl = !empty($currentBranch?->logo_path) ? Storage::url($currentBranch->logo_path) : null;

        $reportData = ReportHelper::getARAgingReport(
            $this->selectedBranchId,
            $this->search,
            $this->selectedCustomerId,
            $this->agingGroupFilter,
            $this->dateFrom,
            $this->dateTo
        );

        // Paginate report items
        $rawItems = $reportData['items'] ?? [];
        $totalItems = count($rawItems);

        if ($this->perPage > 0) {
            $currentPage = (int) $this->getPage('page');
            $currentPageItems = array_slice($rawItems, max(0, ($currentPage - 1) * $this->perPage), $this->perPage);
            $paginatedItems = new LengthAwarePaginator(
                $currentPageItems,
                $totalItems,
                $this->perPage,
                $currentPage,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => 'page',
                ]
            );
        } else {
            $paginatedItems = new LengthAwarePaginator(
                $rawItems,
                $totalItems,
                max(1, $totalItems),
                1,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => 'page',
                ]
            );
        }

        $reportData['paginated_items'] = $paginatedItems;

        return view('livewire.pos-reports-ar-aging', [
            'branches' => $branches,
            'customers' => Customer::orderBy('name')->get(),
            'currentBranch' => $currentBranch,
            'selectedLogoUrl' => $selectedLogoUrl,
            'reportData' => $reportData,
        ])->layout('layouts.pos', ['title' => 'Laporan Piutang Usaha (AR Aging) — POS']);
    }
}
