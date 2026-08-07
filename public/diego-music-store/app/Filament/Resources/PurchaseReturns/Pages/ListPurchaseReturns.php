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

                    ViewField::make('items_return_view')
                        ->view('filament.components.purchase-return-partial-form')
                        ->visible(fn($get) => filled($get('purchase_transaction_id'))),

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
                ->action(function (array $data) {
                    $pt = \App\Models\PurchaseTransaction::findOrFail($data['purchase_transaction_id']);
                    $rawItems = $data['return_items'] ?? request()->input('return_items', []);

                    $itemsToReturn = [];
                    foreach ($rawItems as $detailId => $qty) {
                        $q = (int) $qty;
                        if ($q > 0) {
                            $itemsToReturn[] = [
                                'purchase_transaction_detail_id' => $detailId,
                                'quantity'                       => $q,
                            ];
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
