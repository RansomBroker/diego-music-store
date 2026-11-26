<?php

namespace App\Filament\Resources\Suppliers\Pages;

use App\Actions\Supplier\CreateSupplier;
use App\Filament\Resources\Suppliers\SupplierResource;
use App\Models\Supplier;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ListSuppliers extends ListRecords
{
    protected static string $resource = SupplierResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->badge(fn (): int => Supplier::count()),

            'supplier' => Tab::make('Supplier')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', Supplier::STATUS_SUPPLIER))
                ->badge(fn (): int => Supplier::where('status', Supplier::STATUS_SUPPLIER)->count())
                ->badgeColor('info'),

            'vendor' => Tab::make('Vendor')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', Supplier::STATUS_VENDOR))
                ->badge(fn (): int => Supplier::where('status', Supplier::STATUS_VENDOR)->count())
                ->badgeColor('warning'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('import')
                ->label('Import Excel / CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->visible(fn (): bool => SupplierResource::canCreate())
                ->modalHeading('Import Data Supplier')
                ->modalWidth('4xl')
                ->modalSubmitAction(false)
                ->modalCancelAction(false)
                ->modalFooterActions([])
                ->modalContent(fn () => view('filament.components.supplier-import-modal')),
            CreateAction::make()
                ->modalWidth('2xl')
                ->using(fn (array $data): Model => app(CreateSupplier::class)->execute($data)),
        ];
    }
}
