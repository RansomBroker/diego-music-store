<?php

namespace App\Filament\Resources\StockOpnamePeriods\Pages;

use App\Filament\Resources\StockOpnamePeriods\StockOpnamePeriodResource;
use App\Models\StockOpnamePeriod;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStockOpnamePeriod extends EditRecord
{
    protected static string $resource = StockOpnamePeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn ($record) => $record->status === StockOpnamePeriod::STATUS_DRAFT
                    && $record->assignments()->doesntExist()),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole(['owner', 'admin', 'super_admin', 'Owner', 'Admin', 'Super Admin'])) {
            abort(403);
        }

        $data['branch_id'] = $this->record->branch_id;
        $data['period'] = $this->record->period->toDateString();
        $data['status'] = $this->record->status;
        $data['created_by'] = $this->record->created_by;

        return $data;
    }
}
