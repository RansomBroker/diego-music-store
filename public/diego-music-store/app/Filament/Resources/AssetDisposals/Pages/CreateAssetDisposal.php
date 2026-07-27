<?php

namespace App\Filament\Resources\AssetDisposals\Pages;

use App\Filament\Resources\AssetDisposals\AssetDisposalResource;
use App\Models\Asset;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAssetDisposal extends CreateRecord
{
    protected static string $resource = AssetDisposalResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $asset = Asset::find($data['asset_id']);
        if ($asset) {
            $data['book_value'] = $asset->book_value;
            $isSale = ($data['disposal_type'] ?? 'sale') === 'sale';
            $disposalAmount = $isSale ? (float) ($data['disposal_amount'] ?? 0) : 0.0;
            $data['gain_loss_amount'] = $disposalAmount - $data['book_value'];
        }

        return parent::handleRecordCreation($data);
    }
}
