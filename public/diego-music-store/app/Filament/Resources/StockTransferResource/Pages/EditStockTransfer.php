<?php

namespace App\Filament\Resources\StockTransferResource\Pages;

use App\Filament\Resources\StockTransferResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStockTransfer extends EditRecord
{
    protected static string $resource = StockTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn ($record) => in_array($record->status, ['DRAFT'])),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $totalQty = 0;
        $totalCost = 0;
        
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $qty = (int) ($item['qty'] ?? 0);
                $cost = (float) ($item['unit_cost'] ?? 0);
                $totalQty += $qty;
                $totalCost += ($qty * $cost);
            }
        }
        
        $data['total_qty'] = $totalQty;
        $data['total_cost'] = $totalCost;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
