<?php

namespace App\Filament\Pages\Reports;

use App\Actions\Procurement\GeneratePurchaseReport;
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

class PurchaseReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static \UnitEnum|string|null $navigationGroup = 'Pembelian';

    protected static ?string $navigationLabel = 'Laporan Pembelian';

    protected static ?string $title = 'Laporan Pembelian';

    protected string $view = 'filament.pages.reports.purchase-report';

    public ?array $data = [];
    public ?array $selectedPurchaseDetail = null;

    public function mount(): void
    {
        $this->form->fill([
            'from_date'      => now()->startOfMonth()->format('Y-m-d'),
            'to_date'        => now()->format('Y-m-d'),
            'branch_id'      => null,
            'supplier_id'    => null,
            'purchase_type'  => 'all',
            'payment_status' => 'all',
            'mode'           => 'summary',
            'search'         => null,
        ]);
    }

    public function openPurchaseDetailModal(int $id): void
    {
        $pt = \App\Models\PurchaseTransaction::with(['supplier', 'branch', 'details.productVariant.product', 'details.unit'])->find($id);
        if (!$pt) {
            return;
        }

        $items = [];
        foreach ($pt->details as $d) {
            $variant = $d->productVariant;
            $product = $variant?->product;
            $items[] = [
                'sku'          => $variant?->sku ?: '-',
                'product_name' => ($product?->name ?? 'Produk') . ($variant?->name && $variant->name !== 'Standard' ? " ({$variant->name})" : ''),
                'qty'          => (float) ($d->qty_received ?: $d->qty_po),
                'unit'         => $d->unit?->name ?? 'Pcs',
                'price'        => (float) $d->price,
                'discount'     => (float) ($d->discount ?? 0),
                'subtotal'     => (float) $d->subtotal,
            ];
        }

        $this->selectedPurchaseDetail = [
            'transaction_no' => $pt->transaction_no,
            'invoice_number' => $pt->invoice_number ?: '-',
            'date'           => \Illuminate\Support\Carbon::parse($pt->transaction_date)->format('d/m/Y'),
            'supplier_name'  => $pt->supplier?->name ?? 'Umum',
            'purchase_type'  => $pt->purchase_type,
            'grand_total'    => (float) $pt->grand_total,
            'subtotal'       => (float) $pt->subtotal,
            'discount'       => (float) $pt->discount,
            'tax'            => (float) $pt->tax_amount,
            'shipping'       => (float) $pt->shipping_cost,
            'items'          => $items,
        ];

        $this->dispatch('open-modal', id: 'purchase-detail-modal');
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
                Section::make('Parameter & Filter Laporan Pembelian')
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

                            Select::make('purchase_type')
                                ->label('Tipe Pembelian')
                                ->options([
                                    'all'    => 'Semua Tipe (Tunai & Kredit)',
                                    'Tunai'  => 'Tunai',
                                    'Kredit' => 'Kredit (Hutang)',
                                ])
                                ->default('all')
                                ->live(),

                            Select::make('payment_status')
                                ->label('Status Pelunasan')
                                ->options([
                                    'all'     => 'Semua Status',
                                    'paid'    => 'Lunas',
                                    'partial' => 'Pelunasan Sebagian',
                                    'unpaid'  => 'Belum Lunas',
                                ])
                                ->default('all')
                                ->live(),

                            Select::make('mode')
                                ->label('Mode Tampilan Data')
                                ->options([
                                    'summary' => 'Ringkasan Faktur Pembelian',
                                    'detail'  => 'Rincian Detail Produk per Faktur',
                                ])
                                ->default('summary')
                                ->live(),

                            TextInput::make('search')
                                ->label('Pencarian')
                                ->placeholder('Cari No. Faktur / Supplier...')
                                ->live(debounce: 500),
                        ]),
                    ]),
            ]);
    }

    public function getReportDataProperty(): array
    {
        $state = $this->form->getRawState();

        $fromDate      = $state['from_date'] ?? now()->startOfMonth()->format('Y-m-d');
        $toDate        = $state['to_date'] ?? now()->format('Y-m-d');
        $branchId      = !empty($state['branch_id']) ? (int) $state['branch_id'] : null;
        $supplierId    = !empty($state['supplier_id']) ? (int) $state['supplier_id'] : null;
        $purchaseType  = $state['purchase_type'] ?? 'all';
        $paymentStatus = $state['payment_status'] ?? 'all';
        $mode          = $state['mode'] ?? 'summary';
        $search        = $state['search'] ?? null;

        return (new GeneratePurchaseReport())->execute(
            $fromDate,
            $toDate,
            $branchId,
            $supplierId,
            $purchaseType,
            $paymentStatus,
            $mode,
            $search
        );
    }

    public function printPdf()
    {
        $data = $this->report_data;

        $pdf = Pdf::loadView('reports.purchase-report-pdf', [
            'data' => $data,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'Laporan-Pembelian-' . ($data['from_date'] ?? '') . '-s.d.-' . ($data['to_date'] ?? '') . '.pdf'
        );
    }

    public function exportExcel()
    {
        $data = $this->report_data;
        $filename = 'Laporan-Pembelian-' . ($data['from_date'] ?? '') . '-s.d.-' . ($data['to_date'] ?? '') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, ['DIEGO MUSIC STORE - LAPORAN PEMBELIAN']);
            fputcsv($file, ['Periode:', $data['from_date'] . ' s.d. ' . $data['to_date']]);
            fputcsv($file, ['Cabang:', $data['branch_name']]);
            fputcsv($file, ['Supplier:', $data['supplier_name']]);
            fputcsv($file, ['Tipe Pembelian:', $data['purchase_type']]);
            fputcsv($file, []);

            if ($data['mode'] === 'detail') {
                fputcsv($file, ['No. Faktur', 'Tanggal', 'Supplier', 'Tipe', 'SKU', 'Nama Produk', 'Qty', 'Satuan', 'Harga Satuan (Rp)', 'Diskon Item (Rp)', 'Subtotal Item (Rp)']);
                foreach ($data['purchases'] as $p) {
                    foreach ($p['items'] as $item) {
                        fputcsv($file, [
                            $p['transaction_no'],
                            $p['date'],
                            $p['supplier_name'],
                            $p['purchase_type'],
                            $item['sku'],
                            $item['product_name'],
                            $item['qty'],
                            $item['unit'],
                            $item['price'],
                            $item['discount'],
                            $item['subtotal'],
                        ]);
                    }
                }
            } else {
                fputcsv($file, ['No. Faktur', 'No. Inv Supplier', 'Tanggal', 'Jatuh Tempo', 'Supplier', 'Tipe', 'Status Bayar', 'Subtotal (Rp)', 'Diskon (Rp)', 'Pajak (Rp)', 'Ongkir (Rp)', 'Grand Total (Rp)', 'Terbayar (Rp)', 'Sisa Hutang (Rp)']);
                foreach ($data['purchases'] as $p) {
                    fputcsv($file, [
                        $p['transaction_no'],
                        $p['invoice_number'],
                        $p['date'],
                        $p['due_date'],
                        $p['supplier_name'],
                        $p['purchase_type'],
                        $p['payment_status'],
                        $p['subtotal'],
                        $p['discount'],
                        $p['tax'],
                        $p['shipping'],
                        $p['grand_total'],
                        $p['paid_amount'],
                        $p['unpaid_amount'],
                    ]);
                }
            }

            fputcsv($file, []);
            fputcsv($file, ['RINGKASAN TOTAL PEMBELIAN']);
            fputcsv($file, ['Total Transaksi:', $data['total_transactions']]);
            fputcsv($file, ['Total Qty Produk:', $data['total_qty']]);
            fputcsv($file, ['Total Subtotal:', $data['total_subtotal']]);
            fputcsv($file, ['Total Diskon:', $data['total_discount']]);
            fputcsv($file, ['Total Pajak:', $data['total_tax']]);
            fputcsv($file, ['Total Ongkir:', $data['total_shipping']]);
            fputcsv($file, ['Grand Total Pembelian:', $data['total_grand_total']]);
            fputcsv($file, ['Total Terbayar:', $data['total_paid']]);
            fputcsv($file, ['Total Sisa Hutang:', $data['total_unpaid']]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
