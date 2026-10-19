<?php

namespace App\Filament\Resources\StockOpnamePresets\Pages;

use App\Filament\Resources\StockOpnamePresets\StockOpnamePresetResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStockOpnamePreset extends EditRecord
{
    protected static string $resource = StockOpnamePresetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn ($record) => $record->assignments()->doesntExist()),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole(['owner', 'admin', 'super_admin', 'Owner', 'Admin', 'Super Admin'])) {
            abort(403);
        }

        $data['branch_id'] = $this->record->branch_id;

        return $data;
    }
}
