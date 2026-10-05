<?php

namespace App\Filament\Resources\StockTransferResource\Pages;

use App\Actions\StockTransfer\ApproveStockTransfer;
use App\Actions\StockTransfer\CancelStockTransfer;
use App\Actions\StockTransfer\CompleteStockTransfer;
use App\Actions\StockTransfer\SubmitStockTransfer;
use App\Filament\Resources\StockTransferResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewStockTransfer extends ViewRecord
{
    protected static string $resource = StockTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('ajukan')
                ->label('Ajukan')
                ->icon('heroicon-o-paper-airplane')
                ->color('warning')
                ->visible(fn ($record) => $record->status === 'DRAFT')
                ->requiresConfirmation()
                ->modalHeading('Ajukan Transfer Barang')
                ->modalDescription('Apakah Anda yakin ingin mengajukan transfer ini ke status PENDING?')
                ->action(function ($record) {
                    try {
                        app(SubmitStockTransfer::class)->execute($record);
                        Notification::make()->title('Transfer Berhasil Diajukan')->success()->send();
                        $this->refreshFormData(['status', 'submitted_at']);
                    } catch (\Exception $e) {
                        Notification::make()->title('Gagal Mengajukan')->body($e->getMessage())->danger()->send();
                    }
                }),

            Actions\Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('info')
                ->visible(fn ($record) => $record->status === 'PENDING')
                ->requiresConfirmation()
                ->modalHeading('Setujui Transfer Barang')
                ->modalDescription('Apakah Anda yakin ingin menyetujui transfer ini?')
                ->action(function ($record) {
                    try {
                        app(ApproveStockTransfer::class)->execute($record);
                        Notification::make()->title('Transfer Berhasil Disetujui')->success()->send();
                        $this->refreshFormData(['status', 'approved_at']);
                    } catch (\Exception $e) {
                        Notification::make()->title('Gagal Menyetujui')->body($e->getMessage())->danger()->send();
                    }
                }),

            Actions\Action::make('proses_transfer')
                ->label('Proses Transfer')
                ->icon('heroicon-o-truck')
                ->color('success')
                ->visible(fn ($record) => $record->status === 'APPROVED')
                ->requiresConfirmation()
                ->modalHeading('Proses Transfer Barang')
                ->modalDescription('Stok fisik akan dipindahkan dan jurnal akuntansi 4 sisi akan otomatis diposting. Lanjutkan?')
                ->action(function ($record) {
                    try {
                        app(CompleteStockTransfer::class)->execute($record);
                        Notification::make()->title('Transfer Selesai')->body('Stok telah dipindahkan dan Jurnal Akuntansi berhasil diposting.')->success()->send();
                        $this->refreshFormData(['status', 'completed_at', 'journal_entry_id']);
                    } catch (\Exception $e) {
                        Notification::make()->title('Gagal Memproses')->body($e->getMessage())->danger()->send();
                    }
                }),

            Actions\Action::make('cancel')
                ->label('Batalkan')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn ($record) => in_array($record->status, ['DRAFT', 'PENDING']))
                ->requiresConfirmation()
                ->modalHeading('Batalkan Transfer')
                ->modalDescription('Apakah Anda yakin ingin membatalkan transaksi transfer ini?')
                ->action(function ($record) {
                    try {
                        app(CancelStockTransfer::class)->execute($record);
                        Notification::make()->title('Transfer Dibatalkan')->success()->send();
                        $this->refreshFormData(['status', 'cancelled_at']);
                    } catch (\Exception $e) {
                        Notification::make()->title('Gagal Membatalkan')->body($e->getMessage())->danger()->send();
                    }
                }),

            Actions\EditAction::make()
                ->visible(fn ($record) => in_array($record->status, ['DRAFT'])),
        ];
    }
}
