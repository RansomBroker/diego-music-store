<?php

namespace App\Filament\Resources\PurchaseReturns\Pages;

use App\Filament\Resources\PurchaseReturns\PurchaseReturnResource;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPurchaseReturns extends ListRecords
{
    protected static string $resource = PurchaseReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_return')
                ->label('Buat Retur Pembelian')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->modalHeading('Retur Pembelian Barang ke Supplier')
                ->modalDescription('Pilih transaksi pembelian supplier dan tentukan jumlah unit barang yang dikembalikan. Stok persediaan dan tagihan hutang supplier akan otomatis disesuaikan.')
                ->modalWidth('3xl')
                ->form([
                    Select::make('purchase_transaction_id')
                        ->label('Pilih Transaksi Pembelian Supplier')
                        ->options(function () {
                            $branchId = \App\Helpers\BranchHelper::getActiveBranchId();
                            return \App\Models\PurchaseTransaction::with('supplier')
                                ->where('status', 'posted')
                                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                                ->orderBy('id', 'desc')
                                ->get()
                                ->mapWithKeys(function ($pt) {
                                    $supplierName = $pt->supplier?->name ?: 'No Supplier';
                                    return [$pt->id => "{$pt->transaction_no} - {$supplierName} (Tgl: {$pt->transaction_date->format('d/m/Y')})"];
                                });
                        })
                        ->required()
                        ->searchable()
                        ->live(),

                    ViewField::make('return_items')
                        ->view('filament.components.purchase-return-partial-form')
                        ->default([])
                        ->dehydrated(true)
                        ->visible(fn($get) => filled($get('purchase_transaction_id'))),

                    Select::make('return_type')
                        ->label('Metode Penyelesaian Retur')
                        ->options([
                            'invoice_deduction' => 'Penyesuaian Faktur (Potong Hutang Tempo)',
                            'refund'            => 'Refund Dana (Pengembalian Kas / Rekening Bank)',
                            'replacement'       => 'Tukar Guling (Penggantian Barang Baru)',
                            'supplier_credit'   => 'Saldo Deposit Supplier (Kredit untuk Belanja Nanti)',
                        ])
                        ->default('invoice_deduction')
                        ->required()
                        ->live()
                        ->helperText(fn($state) => match ($state) {
                            'invoice_deduction' => 'Mengurangi sisa tagihan hutang faktur pembelian kredit ini secara otomatis.',
                            'refund'            => 'Supplier mengembalikan dana secara tunai/transfer ke akun kas/bank toko Anda.',
                            'replacement'       => 'Barang cacat diganti dengan unit baru yang sama (kuantitas dan nilai stok seimbang, tanpa perubahan kas/hutang).',
                            'supplier_credit'   => 'Nominal retur dicatat sebagai saldo kredit nota / deposit di supplier untuk memotong pesanan berikutnya.',
                            default             => '',
                        }),

                    Select::make('refund_account_id')
                        ->label('Pilih Akun Kas / Bank Penerima Dana Refund')
                        ->placeholder('-- Pilih Akun Kas atau Rekening Bank --')
                        ->options(function () {
                            $accounts = \App\Models\Account::where('is_header', false)
                                ->where('is_active', true)
                                ->where('classification', 'asset')
                                ->where(function ($sub) {
                                    $sub->where('name', 'like', '%kas%')
                                        ->orWhere('name', 'like', '%bank%');
                                })
                                ->orderBy('code', 'asc')
                                ->pluck('name', 'id')
                                ->toArray();

                            if (empty($accounts)) {
                                $accounts = \App\Models\Account::where('is_header', false)
                                    ->where('is_active', true)
                                    ->where('classification', 'asset')
                                    ->orderBy('code', 'asc')
                                    ->pluck('name', 'id')
                                    ->toArray();
                            }
                            return $accounts;
                        })
                        ->required(fn($get) => $get('return_type') === 'refund')
                        ->visible(fn($get) => $get('return_type') === 'refund')
                        ->searchable()
                        ->preload(),

                    Select::make('replacement_status')
                        ->label('Status Penerimaan Barang Pengganti')
                        ->options([
                            'received' => 'Langsung Diterima (Barang baru pengganti langsung masuk stok)',
                            'pending'  => 'Menunggu Pengiriman (Barang pengganti belum tiba dari supplier)',
                        ])
                        ->default('received')
                        ->required(fn($get) => $get('return_type') === 'replacement')
                        ->visible(fn($get) => $get('return_type') === 'replacement'),

                    Textarea::make('reason')
                        ->label('Alasan Retur ke Supplier / Catatan')
                        ->placeholder('Contoh: Barang cacat dari pabrik, barang tidak sesuai spesifikasi order, dll.')
                        ->required(),

                    Select::make('status')
                        ->label('Status Retur')
                        ->options([
                            'posted' => 'Posting (Selesai & Update Stok/Jurnal Langsung)',
                            'draft'  => 'Draft (Simpan Tanpa Update Stok/Jurnal)',
                        ])
                        ->default('posted')
                        ->required(),
                ])
                ->action(function (array $data, $livewire = null) {
                    $pt = \App\Models\PurchaseTransaction::findOrFail($data['purchase_transaction_id']);
                    $rawItems = $data['return_items'] 
                        ?? data_get($livewire, 'mountedActions.0.data.return_items')
                        ?? data_get($livewire, 'mountedActionData.return_items')
                        ?? data_get($livewire, 'mountedActionsData.0.return_items')
                        ?? request()->input('return_items', []);

                    $itemsToReturn = [];
                    if (is_array($rawItems)) {
                        foreach ($rawItems as $key => $val) {
                            if (is_array($val) && isset($val['purchase_transaction_detail_id'])) {
                                $q = (int) ($val['quantity'] ?? 0);
                                if ($q > 0) {
                                    $itemsToReturn[] = [
                                        'purchase_transaction_detail_id' => $val['purchase_transaction_detail_id'],
                                        'quantity'                       => $q,
                                    ];
                                }
                            } else {
                                $q = (int) $val;
                                if ($q > 0) {
                                    $itemsToReturn[] = [
                                        'purchase_transaction_detail_id' => $key,
                                        'quantity'                       => $q,
                                    ];
                                }
                            }
                        }
                    }

                    if (empty($itemsToReturn)) {
                        Notification::make()
                            ->title('Retur Gagal')
                            ->body('Masukkan minimal 1 unit barang yang akan dikembalikan ke supplier.')
                            ->danger()
                            ->send();
                        return;
                    }

                    try {
                        app(\App\Actions\Purchases\CreatePurchaseReturn::class)->execute([
                            'purchase_transaction_id' => $pt->id,
                            'reason'                  => $data['reason'] ?? 'Retur pembelian ke supplier',
                            'status'                  => $data['status'] ?? 'posted',
                            'return_type'             => $data['return_type'] ?? 'invoice_deduction',
                            'refund_account_id'       => $data['refund_account_id'] ?? null,
                            'replacement_status'      => $data['replacement_status'] ?? 'none',
                            'items'                   => $itemsToReturn,
                        ]);

                        Notification::make()
                            ->title('Retur Pembelian Supplier Berhasil')
                            ->body('Barang berhasil dikurangi dari stok dan penyesuaian hutang / kas supplier telah dicatat.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal Memproses Retur Pembelian')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
