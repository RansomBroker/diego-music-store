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
            \Filament\Actions\Action::make('import')
                ->label('Import Saldo Awal Hutang')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
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
