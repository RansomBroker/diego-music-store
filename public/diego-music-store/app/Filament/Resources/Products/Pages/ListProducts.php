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
            \Filament\Actions\Action::make('printAllBarcodes')
                ->label('Cetak Semua Barcode')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->form(\App\Helpers\ProductHelper::getBarcodePrintFormSchema())
                ->modalWidth('2xl')
                ->extraModalActions([
                    \Filament\Actions\Action::make('downloadAllPdf')
                        ->label('Download PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->action(function ($livewire, array $data) {
                            $formState = method_exists($livewire, 'getMountedActionForm') && $livewire->getMountedActionForm()
                                ? ($livewire->getMountedActionForm()->getRawState() ?? $livewire->getMountedActionForm()->getState(afterValidate: false))
                                : $data;

                            $variants = \App\Models\ProductVariant::with('product')->get();
                            $queue = [];
                            foreach ($variants as $variant) {
                                $product = $variant->product;
                                if (!$product) continue;
                                $name = $product->name;
                                if ($variant->name) {
                                    $name .= ' (' . $variant->name . ')';
                                }
                                $queue[] = [
                                    'variant_id' => $variant->id,
                                    'name'       => $name,
                                    'sku'        => $variant->sku ?? '00000',
                                    'barcode'    => $variant->barcode ?? $variant->sku ?? '',
                                    'price'      => $variant->price,
                                    'qty'        => 1,
                                ];
                            }
                            if (empty($queue)) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Tidak Ada Barcode')
                                    ->body('Tidak ada varian produk yang memiliki barcode untuk didownload.')
                                    ->warning()
                                    ->send();
                                return null;
                            }
                            return \App\Helpers\ProductHelper::generateBarcodePdfResponse($queue, $formState);
                        }),
                ])
                ->action(function ($livewire, array $data) {
                    $variants = \App\Models\ProductVariant::with('product')->get();
                    $queue = [];
                    foreach ($variants as $variant) {
                        $product = $variant->product;
                        if (!$product) continue;
                        $name = $product->name;
                        if ($variant->name) {
                            $name .= ' (' . $variant->name . ')';
                        }
                        $queue[] = [
                            'variant_id' => $variant->id,
                            'name'       => $name,
                            'sku'        => $variant->sku ?? '00000',
                            'barcode'    => $variant->barcode ?? $variant->sku ?? '',
                            'price'      => $variant->price,
                            'qty'        => 1,
                        ];
                    }
                    if (empty($queue)) {
                        \Filament\Notifications\Notification::make()
                            ->title('Tidak Ada Barcode')
                            ->body('Tidak ada varian produk yang memiliki barcode untuk dicetak.')
                            ->warning()
                            ->send();
                        return;
                    }
                    $params = \App\Helpers\ProductHelper::resolveLayoutParams($data);
                    $payload = base64_encode(json_encode(array_merge($params, [
                        'queue' => $queue,
                    ])));
                    $livewire->js("window.open('/pos/barcode-print/sheet?data={$payload}', '_blank')");
                }),
            CreateAction::make()
                ->modalWidth('8xl')
                ->using(fn (array $data): Model => app(CreateProductAction::class)->execute($data)->variants()->first()),
        ];
    }
}

