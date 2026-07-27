<?php

namespace App\Filament\Pages\Reports;

use App\Actions\Procurement\GenerateSupplierPaymentReport;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SupplierPaymentReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static \UnitEnum|string|null $navigationGroup = 'Pembelian';

    protected static ?string $navigationLabel = 'Laporan Pelunasan Hutang';

    protected static ?string $title = 'Laporan Pelunasan Hutang Supplier';

    protected string $view = 'filament.pages.reports.supplier-payment-report';

    public ?array $data = [];
    public ?array $selectedPaymentDetail = null;

    public function mount(): void
    {
        $this->form->fill([
            'from_date'   => now()->startOfMonth()->format('Y-m-d'),
            'to_date'     => now()->format('Y-m-d'),
            'branch_id'   => null,
            'supplier_id' => null,
            'account_id'  => null,
            'mode'        => 'summary',
            'search'      => null,
        ]);
    }

    public function openPaymentDetailModal(int $id): void
    {
        $pay = \App\Models\SupplierPayment::with(['supplier', 'branch', 'account', 'items.purchaseTransaction'])->find($id);
        if (!$pay) {
            return;
        }

        $items = [];
        foreach ($pay->items as $item) {
            $pt = $item->purchaseTransaction;
            $items[] = [
                'transaction_no'    => $pt?->transaction_no ?? '-',
                'invoice_number'    => $pt?->invoice_number ?? '-',
                'purchase_date'     => $pt?->transaction_date ? \Illuminate\Support\Carbon::parse($pt->transaction_date)->format('d/m/Y') : '-',
                'grand_total'       => (float) ($pt?->grand_total ?? 0),
                'amount_due'        => (float) $item->amount_due,
                'amount_paid'       => (float) $item->amount_paid,
                'remaining_balance' => max(0, (float) $item->amount_due - (float) $item->amount_paid),
            ];
        }

        $this->selectedPaymentDetail = [
            'payment_no'        => $pay->payment_no,
            'payment_date'      => \Illuminate\Support\Carbon::parse($pay->payment_date)->format('d/m/Y'),
            'supplier_name'     => $pay->supplier?->name ?? 'Umum',
            'payment_method'    => $pay->payment_method ?: 'Transfer',
            'account_name'      => $pay->account ? "{$pay->account->code} - {$pay->account->name}" : 'Kas/Bank',
            'payment_reference' => $pay->payment_reference ?: '-',
            'total_amount'      => (float) $pay->total_amount,
            'notes'             => $pay->notes ?: '-',
            'items'             => $items,
        ];

        $this->dispatch('open-modal', id: 'payment-detail-modal');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('printPdf')
                ->label('Cetak / PDF')
                ->icon(Heroicon::OutlinedPrinter)
                ->color('success')
                ->extraAttributes([
                    'class' => '!text-white dark:!text-white',
                ])
                ->action('printPdf'),

            Action::make('exportExcel')
                ->label('Ekspor Excel')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('gray')
                ->action('exportExcel'),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Parameter & Filter Laporan Pelunasan Hutang')
                    ->compact()
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm'      => 4,
                        ])->schema([
                            DatePicker::make('from_date')
                                ->label('Dari Tanggal')
                                ->default(now()->startOfMonth()->format('Y-m-d'))
                                ->required()
                                ->live(),

                            DatePicker::make('to_date')
                                ->label('Sampai Tanggal')
                                ->default(now()->format('Y-m-d'))
                                ->required()
                                ->live(),

                            Select::make('branch_id')
                                ->label('Filter Cabang')
                                ->options(Branch::orderBy('name')->pluck('name', 'id'))
                                ->placeholder('Semua Cabang (Konsolidasi)')
                                ->searchable()
                                ->live(),

                            Select::make('supplier_id')
                                ->label('Pilih Supplier / Vendor')
                                ->options(Supplier::orderBy('name')->pluck('name', 'id'))
                                ->placeholder('Semua Supplier')
                                ->searchable()
                                ->live(),

                            Select::make('account_id')
                                ->label('Akun Pembayaran (Kas/Bank)')
                                ->options(
                                    Account::where('is_active', true)
                                        ->where('is_header', false)
                                        ->where(function ($q) {
                                            $q->where('classification', 'asset')
                                              ->orWhere('code', 'LIKE', '1-1%');
                                        })
                                        ->orderBy('code')
                                        ->get()
                                        ->mapWithKeys(fn ($acc) => [$acc->id => "{$acc->code} - {$acc->name}"])
                                )
                                ->placeholder('Semua Akun Kas/Bank')
                                ->searchable()
                                ->live(),

                            Select::make('mode')
                                ->label('Mode Tampilan Data')
                                ->options([
                                    'summary' => 'Ringkasan Bukti Pelunasan',
                                    'detail'  => 'Rincian Alokasi Faktur Terbayar',
                                ])
                                ->default('summary')
                                ->live(),

                            TextInput::make('search')
                                ->label('Pencarian')
                                ->placeholder('Cari No. Bukti / Ref / Keterangan...')
                                ->live(debounce: 500),
                        ]),
                    ]),
            ]);
    }

    public function getReportDataProperty(): array
    {
        $state = $this->form->getRawState();

        $fromDate   = $state['from_date'] ?? now()->startOfMonth()->format('Y-m-d');
        $toDate     = $state['to_date'] ?? now()->format('Y-m-d');
        $branchId   = !empty($state['branch_id']) ? (int) $state['branch_id'] : null;
        $supplierId = !empty($state['supplier_id']) ? (int) $state['supplier_id'] : null;
        $accountId  = !empty($state['account_id']) ? (int) $state['account_id'] : null;
        $mode       = $state['mode'] ?? 'summary';
        $search     = $state['search'] ?? null;

        return (new GenerateSupplierPaymentReport())->execute(
            $fromDate,
            $toDate,
            $branchId,
            $supplierId,
            $accountId,
            $mode,
            $search
        );
    }

    public function printPdf()
    {
        $data = $this->report_data;

        $pdf = Pdf::loadView('reports.supplier-payment-report-pdf', [
            'data' => $data,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'Laporan-Pelunasan-Hutang-' . ($data['from_date'] ?? '') . '-s.d.-' . ($data['to_date'] ?? '') . '.pdf'
        );
    }

    public function exportExcel()
    {
        $data = $this->report_data;
        $filename = 'Laporan-Pelunasan-Hutang-' . ($data['from_date'] ?? '') . '-s.d.-' . ($data['to_date'] ?? '') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, ['DIEGO MUSIC STORE - LAPORAN PELUNASAN HUTANG SUPPLIER']);
            fputcsv($file, ['Periode:', $data['from_date'] . ' s.d. ' . $data['to_date']]);
            fputcsv($file, ['Cabang:', $data['branch_name']]);
            fputcsv($file, ['Supplier:', $data['supplier_name']]);
            fputcsv($file, ['Akun Pembayaran:', $data['account_name']]);
            fputcsv($file, []);

            if ($data['mode'] === 'detail') {
                fputcsv($file, ['No. Bukti', 'Tanggal Bayar', 'Supplier', 'No. Faktur Beli', 'No. Inv Supplier', 'Total Faktur (Rp)', 'Nominal Dilunasi (Rp)', 'Sisa Hutang (Rp)']);
                foreach ($data['payments'] as $pay) {
                    foreach ($pay['items'] as $item) {
                        fputcsv($file, [
                            $pay['payment_no'],
                            $pay['payment_date'],
                            $pay['supplier_name'],
                            $item['transaction_no'],
                            $item['invoice_number'],
                            $item['grand_total'],
                            $item['amount_paid'],
                            $item['remaining_balance'],
                        ]);
                    }
                }
            } else {
                fputcsv($file, ['No. Bukti', 'Tanggal Pelunasan', 'Supplier', 'Metode Bayar', 'Akun Kas/Bank', 'No. Ref / Transfer', 'Jumlah Faktur', 'Total Nominal (Rp)', 'Keterangan']);
                foreach ($data['payments'] as $pay) {
                    fputcsv($file, [
                        $pay['payment_no'],
                        $pay['payment_date'],
                        $pay['supplier_name'],
                        $pay['payment_method'],
                        $pay['account_name'],
                        $pay['payment_reference'],
                        $pay['items_count'],
                        $pay['total_amount'],
                        $pay['notes'],
                    ]);
                }
            }

            fputcsv($file, []);
            fputcsv($file, ['RINGKASAN TOTAL PELUNASAN HUTANG']);
            fputcsv($file, ['Total Bukti Pembayaran:', $data['total_payments_count']]);
            fputcsv($file, ['Total Faktur Dilunasi:', $data['total_invoices_paid']]);
            fputcsv($file, ['Total Supplier Terbayar:', $data['total_suppliers_paid']]);
            fputcsv($file, ['Grand Total Pelunasan (Rp):', $data['total_amount_paid']]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
