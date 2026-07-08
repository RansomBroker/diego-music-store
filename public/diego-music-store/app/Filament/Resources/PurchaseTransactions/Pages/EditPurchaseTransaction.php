<?php

namespace App\Filament\Resources\PurchaseTransactions\Pages;

use App\Filament\Resources\PurchaseTransactions\PurchaseTransactionResource;
use App\Actions\Procurement\UpdatePurchaseTransaction as UpdateAction;
use App\Actions\Procurement\PostPurchaseTransaction;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;

class EditPurchaseTransaction extends EditRecord
{
    protected static string $resource = PurchaseTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('post')
                ->label('Post Transaksi')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === 'draft')
                ->action(function () {
                    app(PostPurchaseTransaction::class)->execute($this->record);
                    $this->redirect($this->getResource()::getUrl('index'));
                }),
            DeleteAction::make()
                ->visible(fn () => $this->record->status === 'draft'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $pt = $this->record;
        $data['discount_type'] = $pt->discount_type ?? 'fixed';
        $data['discount_value'] = $pt->discount_value ?? 0;
        $data['items'] = [];
        foreach ($pt->details as $item) {
            $data['items'][] = [
                'product_variant_id' => $item->product_variant_id,
                'qty_po' => $item->qty_po,
                'qty_received' => $item->qty_received,
                'unit_id' => $item->unit_id,
                'price' => $item->price,
                'discount_type' => $item->discount_type ?? 'fixed',
                'discount_value' => $item->discount_value ?? 0,
                'tax_rate' => $item->tax_rate,
            ];
        }
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            return app(UpdateAction::class)->execute($record, $data);
        } catch (\InvalidArgumentException $e) {
            Notification::make()
                ->title('Tidak dapat menyimpan perubahan')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw new Halt();
        }
    }

    /**
     * Hide the Save button when the transaction is no longer a draft.
     */
    protected function getFormActions(): array
    {
        if ($this->record->status !== 'draft') {
            return [];
        }

        return parent::getFormActions();
    }
}
