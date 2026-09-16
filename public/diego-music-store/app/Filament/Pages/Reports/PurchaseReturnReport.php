<?php

namespace App\Filament\Pages\Reports;

use App\Actions\Procurement\GeneratePurchaseReturnReport;
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

class PurchaseReturnReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static \UnitEnum|string|null $navigationGroup = 'Pembelian';

    protected static ?string $navigationLabel = 'Laporan Retur Pembelian';

    protected static ?string $title = 'Laporan Retur Pembelian (Buku Besar per Supplier)';

    protected string $view = 'filament.pages.reports.purchase-return-report';

    public ?array $data = [];
    public ?array $selectedReturnDetail = null;

    public function mount(): void
    {
        $this->form->fill([
            'from_date'   => now()->startOfMonth()->format('Y-m-d'),
            'to_date'     => now()->format('Y-m-d'),
            'branch_id'   => null,
            'supplier_id' => null,
            'return_type' => 'all',
            'status'      => 'all',
            'mode'        => 'ledger',
            'search'      => null,
        ]);
    }

    public function openReturnDetailModal(int $id): void
    {
        $ret = \App\Models\PurchaseReturn::with([
            'supplier',
            'branch',
            'purchaseTransaction',
            'refundAccount',
            'items.productVariant.product',
        ])->find($id);

        if (!$ret) {
            return;
        }

        $items = [];
        foreach ($ret->items as $item) {
            $variant = $item->productVariant;
            $product = $variant?->product;
            $items[] = [
                'sku'          => $variant?->sku ?: '-',
                'product_name' => ($product?->name ?? 'Produk') . ($variant?->name && $variant->name !== 'Standard' ? " ({$variant->name})" : ''),
                'qty'          => (int) $item->quantity,
                'unit_price'   => (float) $item->unit_price,
                'total_price'  => (float) $item->total_price,
            ];
        }

        $typeLabels = [
            'replacement'       => 'Tukar Guling (Barang Baru)',
            'invoice_deduction' => 'Penyesuaian Faktur (Potong Hutang)',
            'refund'            => 'Refund Kas/Bank',
            'supplier_credit'   => 'Saldo Deposit Supplier',
        ];

        $rawType = $ret->return_type ?: 'replacement';
        $typeLabel = $typeLabels[$rawType] ?? ucfirst(str_replace('_', ' ', $rawType));
        $refundAccountName = $ret->refundAccount ? "{$ret->refundAccount->code} - {$ret->refundAccount->name}" : '-';

        $this->selectedReturnDetail = [
            'return_no'           => $ret->return_no,
            'transaction_no'      => $ret->purchaseTransaction?->transaction_no ?: '-',
            'date'                => \Illuminate\Support\Carbon::parse($ret->return_date)->format('d/m/Y'),
            'supplier_name'       => $ret->supplier?->name ?: 'Umum',
            'branch_name'         => $ret->branch?->name ?: '-',
            'status'              => $ret->status === 'posted' ? 'Posted (Selesai)' : 'Draft',
            'return_type'         => $rawType,
            'return_type_label'   => $typeLabel,
            'refund_account_name' => $refundAccountName,
            'total_amount'        => (float) $ret->total_amount,
            'reason'              => $ret->reason ?: '-',
            'items'               => $items,
        ];

        $this->dispatch('open-modal', id: 'return-detail-modal');
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
                ->label('Ekspor Excel / CSV')
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
                Section::make('Parameter & Filter Laporan Retur Pembelian')
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
                                ->label('Pilih Supplier')
                                ->options(Supplier::orderBy('name')->pluck('name', 'id'))
                                ->placeholder('Semua Supplier')
                                ->searchable()
                                ->live(),

                            Select::make('return_type')
                                ->label('Metode Penyelesaian')
                                ->options([
                                    'all'               => 'Semua Metode Penyelesaian',
                                    'replacement'       => 'Tukar Guling (Barang Baru)',
                                    'invoice_deduction' => 'Penyesuaian Faktur (Potong Hutang)',
                                    'refund'            => 'Refund Kas/Bank',
                                    'supplier_credit'   => 'Saldo Deposit Supplier',
                                ])
                                ->default('all')
                                ->live(),

                            Select::make('status')
                                ->label('Status Retur')
                                ->options([
                                    'all'    => 'Semua Status (Draft & Posted)',
                                    'posted' => 'Posted (Selesai)',
                                    'draft'  => 'Draft (Belum Diposting)',
                                ])
                                ->default('all')
                                ->live(),

                            Select::make('mode')
                                ->label('Mode Tampilan Data')
                                ->options([
                                    'ledger'   => 'Buku Besar per Supplier (Tiap Supplier Satu Tabel)',
                                    'summary'  => 'Ringkasan Transaksi Dokumen Retur',
                                    'detail'   => 'Rincian Detail Barang per Retur',
                                ])
                                ->default('ledger')
                                ->live(),

                            TextInput::make('search')
                                ->label('Pencarian')
                                ->placeholder('Cari No. Retur / Supplier / SKU / Produk...')
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
        $returnType = $state['return_type'] ?? 'all';
        $status     = $state['status'] ?? 'all';
        $mode       = $state['mode'] ?? 'ledger';
        $search     = $state['search'] ?? null;

        return (new GeneratePurchaseReturnReport())->execute(
            $fromDate,
            $toDate,
            $branchId,
            $supplierId,
            $status,
            $mode,
            $search,
            $returnType
        );
    }

    public function printPdf()
    {
        $data = $this->report_data;

        $pdf = Pdf::loadView('reports.purchase-return-report-pdf', [
            'data' => $data,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'Laporan-Buku-Besar-Retur-' . ($data['from_date'] ?? '') . '-s.d.-' . ($data['to_date'] ?? '') . '.pdf'
        );
    }

    public function exportExcel()
    {
        $data = $this->report_data;
        $filename = 'Laporan-Retur-Pembelian-' . ($data['from_date'] ?? '') . '-s.d.-' . ($data['to_date'] ?? '') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, ['DIEGO MUSIC STORE - LAPORAN RETUR PEMBELIAN (BUKU BESAR SUPPLIER)']);
            fputcsv($file, ['Periode:', $data['from_date'] . ' s.d. ' . $data['to_date']]);
            fputcsv($file, ['Cabang:', $data['branch_name']]);
            fputcsv($file, ['Filter Supplier:', $data['supplier_name']]);
            fputcsv($file, ['Metode Penyelesaian:', $data['return_type_label']]);
            fputcsv($file, ['Status:', $data['status']]);
            fputcsv($file, []);

            if ($data['mode'] === 'ledger' || $data['mode'] === 'by_supplier') {
                foreach ($data['supplier_ledgers'] as $supplier) {
                    fputcsv($file, ['========================================================================================']);
                    fputcsv($file, [
                        'BUKU RETUR SUPPLIER:',
                        $supplier['supplier_name'],
                        'Telp: ' . $supplier['phone'],
                        'Total Transaksi: ' . $supplier['total_transactions'],
                        'Total Unit Diretur: ' . $supplier['total_qty_returned'] . ' Unit',
                        'Total Nilai: Rp ' . number_format($supplier['total_amount'], 0, ',', '.'),
                    ]);
                    fputcsv($file, ['Tanggal', 'No. Retur', 'Ref Transaksi PT', 'Metode Penyelesaian', 'Akun Kas/Bank', 'Status', 'SKU', 'Nama Produk / Barang', 'Qty Retur', 'Harga Satuan (Rp)', 'Total Nilai (Rp)', 'Alasan']);

                    foreach ($supplier['entries'] as $entry) {
                        foreach ($entry['items'] as $item) {
                            fputcsv($file, [
                                $entry['date'],
                                $entry['return_no'],
                                $entry['transaction_no'],
                                $entry['return_type_label'],
                                $entry['refund_account_name'] ?: '-',
                                $entry['status_label'],
                                $item['sku'],
                                $item['product_name'],
                                $item['qty'],
                                $item['unit_price'],
                                $item['total_price'],
                                $entry['reason'],
                            ]);
                        }
                    }

                    fputcsv($file, [
                        'SUBTOTAL ' . $supplier['supplier_name'],
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        'TOTAL UNIT: ' . $supplier['total_qty_returned'] . ' Unit',
                        $supplier['total_qty_returned'],
                        'TOTAL NILAI:',
                        $supplier['total_amount'],
                        '',
                    ]);
                    fputcsv($file, []);
                }
            } elseif ($data['mode'] === 'detail') {
                fputcsv($file, ['No. Retur', 'Ref PT', 'Tanggal', 'Cabang', 'Supplier', 'Metode Penyelesaian', 'Akun Kas/Bank', 'Status', 'SKU', 'Nama Produk', 'Qty Retur', 'Harga Satuan (Rp)', 'Total Retur (Rp)', 'Alasan']);
                foreach ($data['returns'] as $ret) {
                    foreach ($ret['items'] as $item) {
                        fputcsv($file, [
                            $ret['return_no'],
                            $ret['transaction_no'],
                            $ret['return_date'],
                            $ret['branch_name'],
                            $ret['supplier_name'],
                            $ret['return_type_label'],
                            $ret['refund_account_name'] ?: '-',
                            $ret['status_label'],
                            $item['sku'],
                            $item['product_name'],
                            $item['qty'],
                            $item['unit_price'],
                            $item['total_price'],
                            $ret['reason'],
                        ]);
                    }
                }
            } else {
                fputcsv($file, ['No. Retur', 'No. Ref PT', 'Tanggal', 'Cabang', 'Supplier', 'Metode Penyelesaian', 'Akun Kas/Bank', 'Status', 'Total Qty', 'Total Nilai (Rp)', 'Alasan Retur', 'Petugas']);
                foreach ($data['returns'] as $ret) {
                    fputcsv($file, [
                        $ret['return_no'],
                        $ret['transaction_no'],
                        $ret['return_date'],
                        $ret['branch_name'],
                        $ret['supplier_name'],
                        $ret['return_type_label'],
                        $ret['refund_account_name'] ?: '-',
                        $ret['status_label'],
                        $ret['total_qty'],
                        $ret['total_amount'],
                        $ret['reason'],
                        $ret['created_by'],
                    ]);
                }
            }

            fputcsv($file, []);
            fputcsv($file, ['RINGKASAN TOTAL RETUR PEMBELIAN (KONSOLIDASI)']);
            fputcsv($file, ['Total Dokumen Retur:', $data['total_transactions']]);
            fputcsv($file, ['Total Unit Barang Diretur:', $data['total_qty_returned'] . ' Unit']);
            fputcsv($file, ['Total Nilai Retur:', $data['total_return_amount']]);
            fputcsv($file, ['Total Tukar Guling (Unit):', $data['breakdown_by_type']['replacement']['qty']]);
            fputcsv($file, ['Total Penyesuaian Faktur (Rp):', $data['breakdown_by_type']['invoice_deduction']['amount']]);
            fputcsv($file, ['Total Refund Kas/Bank (Rp):', $data['breakdown_by_type']['refund']['amount']]);
            fputcsv($file, ['Total Saldo Deposit (Rp):', $data['breakdown_by_type']['supplier_credit']['amount']]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
