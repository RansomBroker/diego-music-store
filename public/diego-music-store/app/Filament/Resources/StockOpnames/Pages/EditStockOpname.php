<?php

namespace App\Filament\Resources\StockOpnames\Pages;

use App\Filament\Resources\StockOpnames\StockOpnameResource;
use App\Actions\StockOpname\UpdateStockOpname as UpdateStockOpnameAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Model;

class EditStockOpname extends EditRecord
{
    protected static string $resource = StockOpnameResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn ($record) => $record->status === 'draft'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $opname = $this->record;
        $data['items'] = [];
        foreach ($opname->items as $item) {
            $data['items'][] = [
                'product_variant_id' => $item->product_variant_id,
                'system_qty' => $item->system_qty,
                'physical_qty' => $item->physical_qty,
                'difference' => $item->difference,
            ];
        }
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateStockOpnameAction::class)->execute($record, $data);
    }
}
