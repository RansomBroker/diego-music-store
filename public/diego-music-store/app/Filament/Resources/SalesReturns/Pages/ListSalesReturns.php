<?php

namespace App\Filament\Resources\SalesReturns\Pages;

use App\Filament\Resources\SalesReturns\SalesReturnResource;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSalesReturns extends ListRecords
{
    protected static string $resource = SalesReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_return')
                ->label('Buat Retur Penjualan')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->modalHeading('Retur Penjualan Barang')
                ->modalDescription('Pilih faktur transaksi dan tentukan jumlah unit barang yang dikembalikan. Tagihan/dana refund dan stok persediaan akan otomatis disesuaikan.')
                ->modalWidth('3xl')
                ->form([
                    Select::make('sale_id')
                        ->label('Pilih Faktur / Transaksi Penjualan')
                        ->options(function () {
                            $branchId = \App\Helpers\BranchHelper::getActiveBranchId();
                            return \App\Models\Sale::where('status', 'completed')
                                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                                ->orderBy('id', 'desc')
                                ->get()
                                ->pluck('invoice_number', 'id');
                        })
                        ->required()
                        ->searchable()
                        ->live(),

                    ViewField::make('return_items')
                        ->view('filament.components.sales-return-partial-form')
                        ->default([])
                        ->dehydrated(true)
                        ->visible(fn($get) => filled($get('sale_id'))),

                    Textarea::make('reason')
                        ->label('Alasan Retur / Catatan')
                        ->placeholder('Contoh: Barang cacat pabrik, salah spesifikasi, dll.')
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
                    $sale = \App\Models\Sale::findOrFail($data['sale_id']);
                    $rawItems = $data['return_items'] 
                        ?? data_get($livewire, 'mountedActions.0.data.return_items')
                        ?? data_get($livewire, 'mountedActionData.return_items')
                        ?? data_get($livewire, 'mountedActionsData.0.return_items')
                        ?? request()->input('return_items', []);

                    $itemsToReturn = [];
                    if (is_array($rawItems)) {
                        foreach ($rawItems as $key => $val) {
                            if (is_array($val) && isset($val['sale_item_id'])) {
                                $q = (int) ($val['quantity'] ?? 0);
                                if ($q > 0) {
                                    $itemsToReturn[] = [
                                        'sale_item_id' => $val['sale_item_id'],
                                        'quantity'     => $q,
                                    ];
                                }
                            } else {
                                $q = (int) $val;
                                if ($q > 0) {
                                    $itemsToReturn[] = [
                                        'sale_item_id' => $key,
                                        'quantity'     => $q,
                                    ];
                                }
                            }
                        }
                    }

                    if (empty($itemsToReturn)) {
                        Notification::make()
                            ->title('Retur Gagal')
                            ->body('Masukkan minimal 1 unit barang yang akan dikembalikan.')
                            ->danger()
                            ->send();
                        return;
                    }

                    try {
                        app(\App\Actions\Sales\CreateSalesReturn::class)->execute([
                            'sale_id' => $sale->id,
                            'reason'  => $data['reason'] ?? 'Retur penjualan',
                            'status'  => $data['status'] ?? 'posted',
                            'items'   => $itemsToReturn,
                        ]);

                        Notification::make()
                            ->title('Retur Penjualan Sebagian Berhasil')
                            ->body('Barang berhasil dikembalikan ke stok dan refund / penyesuaian jurnal telah dicatat.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal Memproses Retur')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
