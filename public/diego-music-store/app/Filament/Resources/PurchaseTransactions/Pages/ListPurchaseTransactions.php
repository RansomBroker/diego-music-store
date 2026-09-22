<?php

namespace App\Filament\Resources\PurchaseTransactions\Pages;

use App\Actions\Procurement\CreatePurchaseTransaction as CreatePurchaseTransactionAction;
use App\Filament\Resources\PurchaseTransactions\PurchaseTransactionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;

class ListPurchaseTransactions extends ListRecords
{
    protected static string $resource = PurchaseTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('7xl')
                ->using(fn (array $data): Model => app(CreatePurchaseTransactionAction::class)->execute($data)),
        ];
    }
}
