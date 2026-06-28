<?php

namespace App\Filament\Resources\PurchaseTransactions\Pages;

use App\Filament\Resources\PurchaseTransactions\PurchaseTransactionResource;
use App\Actions\Procurement\UpdatePurchaseTransaction as UpdateAction;
use App\Actions\Procurement\PostPurchaseTransaction;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
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
        $data['items'] = [];
        foreach ($pt->details as $item) {
            $data['items'][] = [
                'product_variant_id' => $item->product_variant_id,
                'qty_po' => $item->qty_po,
                'qty_received' => $item->qty_received,
                'unit_id' => $item->unit_id,
                'price' => $item->price,
                'discount' => $item->discount,
                'tax_rate' => $item->tax_rate,
            ];
        }
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateAction::class)->execute($record, $data);
    }
}
