<?php

namespace App\Filament\Resources\AssetDisposals\Pages;

use App\Filament\Resources\AssetDisposals\AssetDisposalResource;
use App\Models\Asset;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAssetDisposals extends ListRecords
{
    protected static string $resource = AssetDisposalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('4xl')
                ->mutateFormDataUsing(function (array $data): array {
                    $asset = Asset::find($data['asset_id'] ?? null);
                    if ($asset) {
                        $data['book_value'] = $asset->book_value;
                        $isSale = ($data['disposal_type'] ?? 'sale') === 'sale';
                        $disposalAmount = $isSale ? (float) ($data['disposal_amount'] ?? 0) : 0.0;
                        $data['gain_loss_amount'] = $disposalAmount - $data['book_value'];
                    }

                    return $data;
                }),
        ];
    }
}
