<?php

namespace App\Helpers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Support\Str;

class ProductHelper
{
    public static function generateUniqueSku(): string
    {
        do {
            $sku = 'SKU-' . strtoupper(Str::random(8));
        } while (ProductVariant::where('sku', $sku)->exists());
        
        return $sku;
    }

    public static function generateUniqueBarcode(): string
    {
        do {
            $barcode = '899' . str_pad(random_int(0, 999999999), 9, '0', STR_PAD_LEFT);
            $sum = 0;
            for ($i = 0; $i < 12; $i++) {
                $sum += (int)$barcode[$i] * ($i % 2 === 0 ? 1 : 3);
            }
            $checkDigit = (10 - ($sum % 10)) % 10;
            $barcode .= $checkDigit;
        } while (ProductVariant::where('barcode', $barcode)->exists());

        return $barcode;
    }

    /**
     * Retrieve stock card data for a product.
     *
     * @param  Product  $product
     * @return array<string, mixed>
     */
    public static function getStockCardData(Product $product): array
    {
        $branches = Branch::where('is_active', true)->get();
        
        $bundleItems = collect();
        $childMovements = [];
        $physicalMovements = [];

        if ($product->isBundle()) {
            $defaultVariant = $product->variants->first();
            if ($defaultVariant) {
                $bundleItems = $defaultVariant->bundleItems()
                    ->with(['childVariant.product', 'childVariant.branchStocks'])
                    ->get();

                foreach ($bundleItems as $item) {
                    $childVariant = $item->childVariant;
                    if ($childVariant) {
                        $childMovements[$childVariant->id] = StockMovement::where('product_variant_id', $childVariant->id)
                            ->with('branch')
                            ->orderBy('created_at', 'desc')
                            ->take(50)
                            ->get();
                    }
                }
            }
        } else {
            foreach ($product->variants as $variant) {
                $physicalMovements[$variant->id] = StockMovement::where('product_variant_id', $variant->id)
                    ->with('branch')
                    ->orderBy('created_at', 'desc')
                    ->take(50)
                    ->get();
            }
        }

        return [
            'branches' => $branches,
            'bundleItems' => $bundleItems,
            'childMovements' => $childMovements,
            'physicalMovements' => $physicalMovements,
        ];
    }

    /**
     * Get the form schema for the barcode printing action modal.
     *
     * @return array
     */
    public static function getBarcodePrintFormSchema(): array
    {
        return [
            \Filament\Forms\Components\Select::make('layout')
                ->label('Format Kertas & Label')
                ->options([
                    '3col' => '3 Kolom (33 x 18 mm)',
                    '2col' => '2 Kolom (40 x 22 mm)',
                    '1col' => '1 Kolom (50 x 30 mm)',
                    'custom' => 'Kustom',
                ])
                ->default('3col')
                ->reactive()
                ->afterStateUpdated(function ($state, $set) {
                    if ($state === '3col') {
                        $set('label_width', 33);
                        $set('label_height', 18);
                        $set('columns', 3);
                        $set('gap_x', 3);
                        $set('gap_y', 3);
                        $set('font_size', 10);
                        $set('barcode_height', 35);
                    } elseif ($state === '2col') {
                        $set('label_width', 40);
                        $set('label_height', 22);
                        $set('columns', 2);
                        $set('gap_x', 4);
                        $set('gap_y', 4);
                        $set('font_size', 11);
                        $set('barcode_height', 40);
                    } elseif ($state === '1col') {
                        $set('label_width', 50);
                        $set('label_height', 30);
                        $set('columns', 1);
                        $set('gap_x', 0);
                        $set('gap_y', 5);
                        $set('font_size', 12);
                        $set('barcode_height', 45);
                    }
                }),

            \Filament\Schemas\Components\Grid::make(3)
                ->schema([
                    \Filament\Forms\Components\TextInput::make('label_width')
                        ->numeric()
                        ->label('Lebar Label (mm)')
                        ->default(33)
                        ->required()
                        ->reactive(),
                    \Filament\Forms\Components\TextInput::make('label_height')
                        ->numeric()
                        ->label('Tinggi Label (mm)')
                        ->default(18)
                        ->required()
                        ->reactive(),
                    \Filament\Forms\Components\TextInput::make('columns')
                        ->numeric()
                        ->label('Kolom')
                        ->default(3)
                        ->required()
                        ->reactive(),
                    \Filament\Forms\Components\TextInput::make('gap_x')
                        ->numeric()
                        ->label('Jarak X (mm)')
                        ->default(3)
                        ->required()
                        ->reactive(),
                    \Filament\Forms\Components\TextInput::make('gap_y')
                        ->numeric()
                        ->label('Jarak Y (mm)')
                        ->default(3)
                        ->required()
                        ->reactive(),
                    \Filament\Forms\Components\TextInput::make('font_size')
                        ->numeric()
                        ->label('Ukuran Font (px)')
                        ->default(10)
                        ->required()
                        ->reactive(),
                    \Filament\Forms\Components\TextInput::make('barcode_height')
                        ->numeric()
                        ->label('Tinggi Barcode (px)')
                        ->default(35)
                        ->required()
                        ->reactive(),
                ]),

            \Filament\Schemas\Components\Grid::make(4)
                ->schema([
                    \Filament\Forms\Components\Toggle::make('show_store')
                        ->label('Nama Toko')
                        ->default(true),
                    \Filament\Forms\Components\Toggle::make('show_name')
                        ->label('Nama Produk')
                        ->default(true),
                    \Filament\Forms\Components\Toggle::make('show_price')
                        ->label('Harga')
                        ->default(true),
                    \Filament\Forms\Components\Toggle::make('show_code')
                        ->label('Kode Barcode')
                        ->default(true),
                ]),
        ];
    }

    /**
     * Resolve layout parameters according to preset or custom choices.
     *
     * @param array $data
     * @return array
     */
    public static function resolveLayoutParams(array $data): array
    {
        $layout = $data['layout'] ?? null;

        if (!$layout && isset($data['columns'])) {
            $cols = intval($data['columns']);
            if ($cols === 1) $layout = '1col';
            elseif ($cols === 2) $layout = '2col';
            elseif ($cols === 3) $layout = '3col';
        }

        if (!$layout) {
            $layout = '3col';
        }

        if ($layout === '1col') {
            $params = [
                'layout'         => '1col',
                'label_width'    => 50,
                'label_height'   => 30,
                'columns'        => 1,
                'gap_x'          => 0,
                'gap_y'          => 5,
                'font_size'      => 12,
                'barcode_height' => 45,
            ];
        } elseif ($layout === '2col') {
            $params = [
                'layout'         => '2col',
                'label_width'    => 40,
                'label_height'   => 22,
                'columns'        => 2,
                'gap_x'          => 4,
                'gap_y'          => 4,
                'font_size'      => 11,
                'barcode_height' => 40,
            ];
        } elseif ($layout === '3col') {
            $params = [
                'layout'         => '3col',
                'label_width'    => 33,
                'label_height'   => 18,
                'columns'        => 3,
                'gap_x'          => 3,
                'gap_y'          => 3,
                'font_size'      => 10,
                'barcode_height' => 35,
            ];
        } else {
            $params = [
                'layout'         => 'custom',
                'label_width'    => intval($data['label_width'] ?? 33),
                'label_height'   => intval($data['label_height'] ?? 18),
                'columns'        => intval($data['columns'] ?? 3),
                'gap_x'          => intval($data['gap_x'] ?? 3),
                'gap_y'          => intval($data['gap_y'] ?? 3),
                'font_size'      => intval($data['font_size'] ?? 10),
                'barcode_height' => intval($data['barcode_height'] ?? 35),
            ];
        }

        $params['show_store'] = (bool)($data['show_store'] ?? true);
        $params['show_name']  = (bool)($data['show_name'] ?? true);
        $params['show_price'] = (bool)($data['show_price'] ?? true);
        $params['show_code']  = (bool)($data['show_code'] ?? true);

        return $params;
    }

    /**
     * Generate the PDF download response for a given queue and data.
     *
     * @param array $queue
     * @param array $data
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public static function generateBarcodePdfResponse(array $queue, array $data)
    {
        $branch = auth()->user()?->branches()?->first();
        $storeTitle = $branch?->store_name ?: 'Diego Music Store';

        $params = self::resolveLayoutParams($data);

        $labelWidth    = $params['label_width'];
        $labelHeight   = $params['label_height'];
        $columns       = $params['columns'];

        $pdfBarcodeWidthMm  = round($labelWidth * 0.85, 1);
        $pdfBarcodeHeightMm = round($labelHeight * 0.40, 1);

        $viewData = array_merge($params, [
            'queue'              => $queue,
            'branch'             => $branch,
            'storeTitle'         => $storeTitle,
            'isPdf'              => true,
            'pdfBarcodeWidthMm'  => $pdfBarcodeWidthMm,
            'pdfBarcodeHeightMm' => $pdfBarcodeHeightMm,
        ]);

        $orientation = $columns <= 1 ? 'landscape' : 'portrait';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::setOption('isRemoteEnabled', true)
            ->setOption('allowedProtocols', [
                'data://' => ['rules' => []],
                'file://' => ['rules' => []],
                'http://' => ['rules' => []],
                'https://' => ['rules' => []],
            ])
            ->setPaper('a4', $orientation)
            ->loadView('pos.barcode-print-sheet', $viewData);

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'barcode-' . date('Y-m-d-H-i-s') . '.pdf'
        );
    }
}
