<?php

namespace App\Filament\Resources\FocusProductResource\Pages;

use App\Actions\FocusProduct\DismissFocusProduct;
use App\Actions\FocusProduct\ResolveFocusProduct;
use App\Filament\Resources\FocusProductResource;
use App\Models\FocusProduct;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewFocusProduct extends ViewRecord
{
    protected static string $resource = FocusProductResource::class;

    protected function getHeaderActions(): array
    {
        /** @var FocusProduct $record */
        $record = $this->getRecord();

        return [
            Action::make('resolve')
                ->label('Tandai Selesai')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (): bool => in_array($record->status, ['ACTIVE', 'EXPIRED']))
                ->form([
                    Textarea::make('resolution_note')
                        ->label('Catatan Penyelesaian')
                        ->placeholder('Contoh: Seluruh stok berhasil terjual habis melalui program promo cuci gudang.')
                        ->required(),
                ])
                ->action(function (array $data) use ($record) {
                    try {
                        app(ResolveFocusProduct::class)->execute($record->id, $data['resolution_note'], auth()->id());
                        Notification::make()->title('Produk Fokus Diselesaikan')->success()->send();
                        $this->refreshFormData(['status', 'resolved_at', 'resolved_by', 'resolution_note']);
                    } catch (\Exception $e) {
                        Notification::make()->title('Gagal Menyelesaikan')->body($e->getMessage())->danger()->send();
                    }
                }),

            Action::make('extend')
                ->label('Ubah Batas Waktu')
                ->icon('heroicon-o-clock')
                ->color('warning')
                ->visible(fn (): bool => in_array($record->status, ['ACTIVE', 'EXPIRED']))
                ->fillForm(fn () => ['active_until' => $record->active_until])
                ->form([
                    DateTimePicker::make('active_until')
                        ->label('Aktif Sampai')
                        ->helperText('Kosongkan jika ingin dibuat tanpa batas waktu.'),
                ])
                ->action(function (array $data) use ($record) {
                    $activeUntil = $data['active_until'];
                    $newStatus = $record->status;
                    if ($record->status === 'EXPIRED' && ($activeUntil === null || Carbon::parse($activeUntil)->isFuture())) {
                        $newStatus = 'ACTIVE';
                    }
                    $record->update([
                        'active_until' => $activeUntil,
                        'status' => $newStatus,
                    ]);
                    Notification::make()->title('Periode Fokus Diperbarui')->success()->send();
                    $this->refreshFormData(['active_until', 'status']);
                }),

            Action::make('dismiss')
                ->label('Abaikan / Dismiss')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (): bool => in_array($record->status, ['ACTIVE', 'EXPIRED']))
                ->form([
                    Textarea::make('dismissal_note')
                        ->label('Alasan Diabaikan')
                        ->placeholder('Contoh: Produk kelas premium, perputaran memang lambat secara alami.')
                        ->required(),
                ])
                ->action(function (array $data) use ($record) {
                    try {
                        app(DismissFocusProduct::class)->execute($record->id, $data['dismissal_note'], auth()->id());
                        Notification::make()->title('Produk Fokus Diabaikan')->success()->send();
                        $this->refreshFormData(['status', 'dismissed_at', 'dismissed_by', 'dismissal_note']);
                    } catch (\Exception $e) {
                        Notification::make()->title('Gagal Mengabaikan')->body($e->getMessage())->danger()->send();
                    }
                }),
        ];
    }
}
