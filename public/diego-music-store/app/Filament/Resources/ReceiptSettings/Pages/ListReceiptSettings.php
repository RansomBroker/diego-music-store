<?php

namespace App\Filament\Resources\ReceiptSettings\Pages;

use App\Actions\Setting\UpdateReceiptSettings;
use App\Filament\Resources\ReceiptSettings\ReceiptSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;

class ListReceiptSettings extends ListRecords
{
    protected static string $resource = ReceiptSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Template Cabang')
                ->modalWidth('7xl')
                ->using(function (array $data): Model {
                    return app(UpdateReceiptSettings::class)->execute((int) $data['branch_id'], $data);
                }),
        ];
    }
}

