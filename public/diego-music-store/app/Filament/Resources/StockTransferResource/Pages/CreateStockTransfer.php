<?php

namespace App\Filament\Resources\StockTransferResource\Pages;

use App\Filament\Resources\StockTransferResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use App\Models\StockTransfer;

class CreateStockTransfer extends CreateRecord
{
    protected static string $resource = StockTransferResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['transfer_number'] = StockTransfer::generateTransferNumber();
        
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
        $data['status'] = 'DRAFT';

        return $data;
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Simpan Draft');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
