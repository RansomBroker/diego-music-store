<?php

namespace App\Filament\Resources\Products\Pages;

use App\Actions\Product\CreateProduct as CreateProductAction;
use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('8xl')
                ->using(fn (array $data): Model => app(CreateProductAction::class)->execute($data)->variants()->first()),
            \Filament\Actions\Action::make('importProducts')
                ->label('Import Excel / CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->modalHeading('Import Data Produk & Saldo Awal Stok')
                ->modalWidth('4xl')
                ->modalSubmitAction(false)
                ->modalCancelAction(false)
                ->modalFooterActions([])
                ->modalContent(fn () => view('filament.components.product-import-modal')),
        ];
    }
}


