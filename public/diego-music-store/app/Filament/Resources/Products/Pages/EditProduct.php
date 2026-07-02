<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use App\Actions\Product\UpdateProduct as UpdateProductAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    public function getMaxContentWidth(): \Filament\Support\Enums\Width | string | null
    {
        return \Filament\Support\Enums\Width::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $product = $this->record;
        
        $variantsCount = $product->variants()->count();
        $firstVariant = $product->variants()->first();
        
        if ($product->isPhysical() && $variantsCount > 1) {
            $data['has_variants'] = true;
            $data['variants'] = [];
            
            foreach ($product->variants as $variant) {
                $data['variants'][] = [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'sku' => $variant->sku,
                    'barcode' => $variant->barcode,
                    'price' => $variant->price,
                    'cost_price' => $variant->cost_price,
                    'hpp' => $variant->hpp,
                    'tier_prices' => $variant->tierPrices()->pluck('price', 'pricing_tier_id')->toArray(),
                ];
            }
        } else {
            $data['has_variants'] = false;
            if ($firstVariant) {
                $data['sku'] = $firstVariant->sku;
                $data['barcode'] = $firstVariant->barcode;
                $data['price'] = $firstVariant->price;
                $data['cost_price'] = $firstVariant->cost_price;
                $data['hpp'] = $firstVariant->hpp;
                
                $data['tier_prices'] = $firstVariant->tierPrices()->pluck('price', 'pricing_tier_id')->toArray();
                
                if ($product->isBundle()) {
                    $data['bundle_items'] = [];
                    foreach ($firstVariant->bundleItems as $item) {
                        $data['bundle_items'][] = [
                            'child_variant_id' => $item->child_variant_id,
                            'quantity' => $item->quantity,
                        ];
                    }
                }
            }
        }
        
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateProductAction::class)->execute($record, $data);
    }
}
