<?php

namespace App\Filament\Concerns;

use App\Actions\Accounting\ResetOpeningBalances;
use App\Actions\Accounting\SeedOpeningBalances;
use App\Helpers\PermissionHelper;
use App\Models\JournalEntry;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

/**
 * TODO(temp): Hapus trait ini beserta penggunaannya di BalanceSheet dan ListJournalEntries
 * setelah angka saldo awal neraca sudah dipastikan sesuai.
 */
trait HasTemporaryOpeningBalanceActions
{
    /**
     * @return array<int, Action>
     */
    protected function temporaryOpeningBalanceHeaderActions(): array
    {
        return [
            Action::make('bypass_opening_balance')
                ->label('Bypass Saldo Awal')
                ->icon('heroicon-o-bolt')
                ->color('warning')
                ->visible(fn (): bool => PermissionHelper::can(Auth::user(), 'bo.journal_entries.create'))
                ->requiresConfirmation()
                ->modalHeading('Bypass Saldo Awal Neraca (Akun Masih 0)')
                ->modalDescription('Akan membuat jurnal OpeningBalance untuk mengisi saldo awal neraca pada akun-akun COA yang mutasinya masih 0 (Hutang Dagang 211101001 tidak ikut dibypass). Lanjutkan?')
                ->modalSubmitActionLabel('Ya, Jalankan Bypass')
                ->action(function (): void {
                    try {
                        $entry = app(SeedOpeningBalances::class)->execute();

                        if ($entry === null) {
                            $alreadyExists = JournalEntry::query()
                                ->where('reference_type', 'OpeningBalance')
                                ->exists();

                            Notification::make()
                                ->title('Bypass Saldo Awal Dilewati')
                                ->body($alreadyExists
                                    ? 'Jurnal Saldo Awal (OpeningBalance) sudah ada. Klik "Reset Saldo Awal" terlebih dahulu jika ingin mengulang.'
                                    : 'Tidak ada akun bersaldo 0 yang dapat dibypass, atau sudah ada jurnal operasional berjalan.')
                                ->warning()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Bypass Saldo Awal Berhasil')
                            ->body("Jurnal {$entry->entry_no} ({$entry->items()->count()} item) berhasil dibuat.")
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Gagal Menjalankan Bypass Saldo Awal')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('reset_opening_balance')
                ->label('Reset Saldo Awal')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->visible(fn (): bool => PermissionHelper::can(Auth::user(), 'bo.journal_entries.delete'))
                ->requiresConfirmation()
                ->modalHeading('Reset Jurnal Saldo Awal (Bypass)')
                ->modalDescription('Hanya menghapus jurnal OpeningBalance hasil bypass saldo awal pada Cabang Pusat. Jurnal InitialStock, InitialDebt, penjualan, pembelian, dan jurnal manual lainnya tidak akan disentuh.')
                ->modalSubmitActionLabel('Ya, Reset Saldo Awal')
                ->action(function (): void {
                    try {
                        $result = app(ResetOpeningBalances::class)->execute();

                        if ($result['deleted_entries'] === 0) {
                            Notification::make()
                                ->title('Tidak Ada Jurnal OpeningBalance')
                                ->body('Belum ada jurnal saldo awal hasil bypass untuk direset.')
                                ->warning()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Reset Saldo Awal Berhasil')
                            ->body(sprintf(
                                'Dihapus: %d jurnal OpeningBalance dan %d item jurnal terkait.',
                                $result['deleted_entries'],
                                $result['deleted_items']
                            ))
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Gagal Reset Saldo Awal')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}

