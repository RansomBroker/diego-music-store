<?php

namespace App\Filament\Resources\PurchaseTransactions\Pages;

use App\Actions\Procurement\CreatePurchaseTransaction as CreatePurchaseTransactionAction;
use App\Actions\Procurement\ResetPurchaseTransactions as ResetPurchaseTransactionsAction;
use App\Filament\Resources\PurchaseTransactions\PurchaseTransactionResource;
use App\Helpers\BranchHelper;
use App\Helpers\PermissionHelper;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ListPurchaseTransactions extends ListRecords
{
    protected static string $resource = PurchaseTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reset_transactions')
                ->label('Reset Transaksi Pembelian')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->visible(false) // Temporarily hidden; restore: fn (): bool => PermissionHelper::can(Auth::user(), 'bo.purchase_transactions.delete')
                ->requiresConfirmation()
                ->modalHeading('Reset Semua Transaksi Pembelian')
                ->modalDescription('Tindakan ini akan menghapus semua transaksi pembelian pada cabang aktif beserta jurnal terkait (Pembelian & Saldo Awal Hutang), mutasi stok pembelian, dan saldo hutang supplier dari transaksi tersebut. Tindakan ini tidak dapat dibatalkan.')
                ->modalSubmitActionLabel('Ya, Hapus & Reset Semua')
                ->action(function (): void {
                    try {
                        $result = app(ResetPurchaseTransactionsAction::class)
                            ->execute(BranchHelper::getActiveBranchId());

                        if ($result['deleted_transactions'] === 0 && $result['deleted_journals'] === 0) {
                            Notification::make()
                                ->title('Tidak ada data untuk direset')
                                ->body('Tidak ditemukan transaksi pembelian atau jurnal terkait pada cabang aktif.')
                                ->warning()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Reset Transaksi Pembelian Berhasil')
                            ->body(sprintf(
                                'Dihapus: %d transaksi pembelian, %d jurnal terkait, dan %d mutasi stok.',
                                $result['deleted_transactions'],
                                $result['deleted_journals'],
                                $result['deleted_stock_movements']
                            ))
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Gagal Reset Transaksi Pembelian')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('import')
                ->label('Import Saldo Awal Hutang')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->visible(fn (): bool => PurchaseTransactionResource::canCreate())
                ->modalHeading('Import Saldo Awal Faktur Hutang Supplier')
                ->modalWidth('4xl')
                ->modalSubmitAction(false)
                ->modalCancelAction(false)
                ->modalFooterActions([])
                ->modalContent(fn () => view('filament.components.supplier-debt-import-modal')),
            CreateAction::make()
                ->modalWidth('7xl')
                ->using(fn (array $data): Model => app(CreatePurchaseTransactionAction::class)->execute($data)),
        ];
    }
}
