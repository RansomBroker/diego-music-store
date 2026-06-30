<?php

namespace App\Filament\Resources\InventoryMutations\Pages;

use App\Filament\Resources\InventoryMutations\InventoryMutationResource;
use App\Actions\InventoryMutation\UpdateInventoryMutation as UpdateInventoryMutationAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Model;

class EditInventoryMutation extends EditRecord
{
    protected static string $resource = InventoryMutationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn ($record) => $record->status === 'draft'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $mutation = $this->record;
        $data['items'] = [];
        foreach ($mutation->items as $item) {
            $data['items'][] = [
                'product_variant_id' => $item->product_variant_id,
                'quantity' => $item->quantity,
            ];
        }
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateInventoryMutationAction::class)->execute($record, $data);
    }
}
