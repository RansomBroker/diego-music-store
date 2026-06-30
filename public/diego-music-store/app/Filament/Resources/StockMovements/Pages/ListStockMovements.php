<?php

namespace App\Filament\Resources\StockMovements\Pages;

use App\Filament\Resources\StockMovements\StockMovementResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;

class ListStockMovements extends ListRecords
{
    protected static string $resource = StockMovementResource::class;

    protected string $view = 'backoffice.inventory.stock-card-ledger';

    public ?array $data = [];

    public function mount(): void
    {
        // Resolve initial parameters from queries and table filters
        $productVariantId = request()->query('product_variant_id') ?? null;
        $branchId = request()->query('branch_id') ?? null;

        $tableFilters = request()->query('tableFilters');
        if (is_array($tableFilters)) {
            if (isset($tableFilters['product_id']['value']) && $tableFilters['product_id']['value']) {
                $productId = $tableFilters['product_id']['value'];
                $productVariantId = \App\Models\ProductVariant::where('product_id', $productId)->first()?->id;
            }
            if (isset($tableFilters['product_variant_id']['value']) && $tableFilters['product_variant_id']['value']) {
                $productVariantId = $tableFilters['product_variant_id']['value'];
            }
            if (isset($tableFilters['branch_id']['value']) && $tableFilters['branch_id']['value']) {
                $branchId = $tableFilters['branch_id']['value'];
            }
        }

        // Fill form state
        $this->form->fill([
            'productVariantId' => $productVariantId,
            'branchId' => $branchId,
            'startDate' => now()->startOfMonth()->format('Y-m-d'),
            'endDate' => now()->endOfMonth()->format('Y-m-d'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('productVariantId')
                    ->label('Pilih Produk & Varian')
                    ->options(function() {
                        return \App\Models\ProductVariant::join('products', 'products.id', '=', 'product_variants.product_id')
                            ->select('product_variants.id', 'products.name as product_name', 'product_variants.name as variant_name', 'product_variants.sku')
                            ->orderBy('products.name')
                            ->get()
                            ->mapWithKeys(fn ($v) => [
                                $v->id => "[{$v->sku}] {$v->product_name}" . ($v->variant_name ? " - {$v->variant_name}" : "")
                            ]);
                    })
                    ->searchable()
                    ->live()
                    ->placeholder('-- Pilih Produk --'),
                
                Select::make('branchId')
                    ->label('Cabang')
                    ->options(\App\Models\Branch::where('is_active', true)->pluck('name', 'id'))
                    ->searchable()
                    ->live()
                    ->placeholder('-- Pilih Cabang --'),

                DatePicker::make('startDate')
                    ->label('Dari Tanggal')
                    ->live(),

                DatePicker::make('endDate')
                    ->label('Sampai Tanggal')
                    ->live(),
            ])
            ->columns(4)
            ->statePath('data');
    }

    public function resetFilters(): void
    {
        $this->form->fill([
            'productVariantId' => null,
            'branchId' => null,
            'startDate' => now()->startOfMonth()->format('Y-m-d'),
            'endDate' => now()->endOfMonth()->format('Y-m-d'),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return []; // No default actions needed
    }

    /**
     * Compute and get all detailed data for the stock card ledger.
     */
    public function getStockCardData(): array
    {
        $productVariantId = $this->data['productVariantId'] ?? null;
        $branchId = $this->data['branchId'] ?? null;
        $startDate = $this->data['startDate'] ?? null;
        $endDate = $this->data['endDate'] ?? null;

        if (!$productVariantId || !$branchId) {
            return [];
        }

        $variant = \App\Models\ProductVariant::with('product.unit')->find($productVariantId);
        $branch = \App\Models\Branch::find($branchId);

        if (!$variant || !$branch) {
            return [];
        }

        // Calculate opening stock before start date
        $inBefore = \App\Models\StockMovement::where('product_variant_id', $productVariantId)
            ->where('branch_id', $branchId)
            ->where('type', 'in')
            ->where('created_at', '<', $startDate . ' 00:00:00')
            ->sum('quantity');

        $outBefore = \App\Models\StockMovement::where('product_variant_id', $productVariantId)
            ->where('branch_id', $branchId)
            ->where('type', 'out')
            ->where('created_at', '<', $startDate . ' 00:00:00')
            ->sum('quantity');

        $openingStock = $inBefore - $outBefore;

        // Fetch movements during the period
        $movements = \App\Models\StockMovement::where('product_variant_id', $productVariantId)
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Calculate running balance for each movement and totals
        $running = $openingStock;
        $totalIn = 0;
        $totalOut = 0;

        $processedMovements = [];
        foreach ($movements as $mv) {
            if ($mv->type === 'in') {
                $running += $mv->quantity;
                $totalIn += $mv->quantity;
            } else {
                $running -= $mv->quantity;
                $totalOut += $mv->quantity;
            }

            $processedMovements[] = [
                'id' => $mv->id,
                'created_at' => $mv->created_at,
                'type' => $mv->type,
                'quantity' => $mv->quantity,
                'reference_label' => $mv->reference_label,
                'running_balance' => $running,
            ];
        }

        // Reverse the chronological order to display newest first in the table
        $processedMovements = array_reverse($processedMovements);

        return [
            'variant' => $variant,
            'branch' => $branch,
            'opening_stock' => $openingStock,
            'closing_stock' => $running,
            'total_in' => $totalIn,
            'total_out' => $totalOut,
            'movements' => $processedMovements,
        ];
    }
}
