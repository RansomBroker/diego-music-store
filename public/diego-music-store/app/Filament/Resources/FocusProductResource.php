<?php

namespace App\Filament\Resources;

use App\Actions\FocusProduct\DismissFocusProduct;
use App\Actions\FocusProduct\ResolveFocusProduct;
use App\Filament\Resources\FocusProductResource\Pages;
use App\Helpers\FocusProductHelper;
use App\Models\Branch;
use App\Models\FocusProduct;
use App\Models\ProductBranchStock;
use App\Models\SaleItem;
use App\Models\StockMovement;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class FocusProductResource extends Resource
{
    protected static ?string $model = FocusProduct::class;

    protected static string|UnitEnum|null $navigationGroup = 'Persediaan';
    protected static ?string $modelLabel = 'Produk Fokus';
    protected static ?string $pluralModelLabel = 'Produk Fokus';
    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('productVariant.product.image_path')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl('/images/default-product.png'),

                Tables\Columns\TextColumn::make('productVariant.product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->description(fn (FocusProduct $record) => $record->productVariant?->name !== 'Standard' ? $record->productVariant?->name : null),

                Tables\Columns\TextColumn::make('productVariant.sku')
                    ->label('SKU')
                    ->badge()
                    ->color('gray')
                    ->copyable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Stok Cabang')
                    ->getStateUsing(function (FocusProduct $record) {
                        $stock = ProductBranchStock::where('branch_id', $record->branch_id)
                            ->where('product_variant_id', $record->product_variant_id)
                            ->value('stock');
                        return ($stock ?? 0) . ' unit';
                    })
                    ->badge()
                    ->color(fn (string $state): string => ((int) $state) > 0 ? 'warning' : 'danger'),

                Tables\Columns\TextColumn::make('sales_qty')
                    ->label('Penjualan (6 Bln)')
                    ->getStateUsing(function (FocusProduct $record) {
                        $summary = FocusProductHelper::getRecentSalesSummary($record->branch_id, $record->product_variant_id, 6);
                        return $summary['total_qty'] . ' unit';
                    }),

                Tables\Columns\TextColumn::make('source')
                    ->label('Sumber')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'RULE' ? 'primary' : 'gray')
                    ->description(fn (FocusProduct $record) => $record->rule?->name),

                Tables\Columns\TextColumn::make('active_from')
                    ->label('Mulai Fokus')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('active_until')
                    ->label('Aktif Sampai')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Tanpa Batas')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ACTIVE' => 'success',
                        'EXPIRED' => 'warning',
                        'RESOLVED' => 'info',
                        'DISMISSED' => 'gray',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('Cabang')
                    ->relationship('branch', 'name'),

                SelectFilter::make('source')
                    ->label('Sumber')
                    ->options([
                        'MANUAL' => 'Manual',
                        'RULE' => 'Rekomendasi Sistem',
                    ]),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'ACTIVE' => 'Aktif (ACTIVE)',
                        'EXPIRED' => 'Kadaluarsa (EXPIRED)',
                        'RESOLVED' => 'Selesai (RESOLVED)',
                        'DISMISSED' => 'Diabaikan (DISMISSED)',
                    ]),
            ])
            ->actions([
                ViewAction::make(),

                ActionGroup::make([
                    Action::make('resolve')
                        ->label('Tandai Selesai')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (FocusProduct $record): bool => in_array($record->status, ['ACTIVE', 'EXPIRED']))
                        ->form([
                            Textarea::make('resolution_note')
                                ->label('Catatan Penyelesaian')
                                ->placeholder('Contoh: Seluruh stok berhasil terjual habis melalui program promo cuci gudang.')
                                ->required(),
                        ])
                        ->action(function (FocusProduct $record, array $data) {
                            try {
                                app(ResolveFocusProduct::class)->execute($record->id, $data['resolution_note'], auth()->id());
                                Notification::make()->title('Produk Fokus Diselesaikan')->success()->send();
                            } catch (\Exception $e) {
                                Notification::make()->title('Gagal Menyelesaikan')->body($e->getMessage())->danger()->send();
                            }
                        }),

                    Action::make('extend')
                        ->label('Perpanjang / Ubah Periode')
                        ->icon('heroicon-o-clock')
                        ->color('warning')
                        ->visible(fn (FocusProduct $record): bool => in_array($record->status, ['ACTIVE', 'EXPIRED']))
                        ->fillForm(fn (FocusProduct $record) => ['active_until' => $record->active_until])
                        ->form([
                            DateTimePicker::make('active_until')
                                ->label('Aktif Sampai')
                                ->helperText('Kosongkan jika ingin dibuat tanpa batas waktu.'),
                        ])
                        ->action(function (FocusProduct $record, array $data) {
                            $activeUntil = $data['active_until'];
                            $newStatus = $record->status;
                            if ($record->status === 'EXPIRED' && ($activeUntil === null || Carbon::parse($activeUntil)->isFuture())) {
                                $newStatus = 'ACTIVE';
                            }
                            $record->update([
                                'active_until' => $activeUntil,
                                'status' => $newStatus,
                            ]);
                            Notification::make()->title('Periode Fokus Diperbarui')->success()->send();
                        }),

                    Action::make('dismiss')
                        ->label('Abaikan / Dismiss')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (FocusProduct $record): bool => in_array($record->status, ['ACTIVE', 'EXPIRED']))
                        ->form([
                            Textarea::make('dismissal_note')
                                ->label('Alasan Diabaikan')
                                ->placeholder('Contoh: Produk kelas premium, perputaran memang lambat secara alami.')
                                ->required(),
                        ])
                        ->action(function (FocusProduct $record, array $data) {
                            try {
                                app(DismissFocusProduct::class)->execute($record->id, $data['dismissal_note'], auth()->id());
                                Notification::make()->title('Produk Fokus Diabaikan')->success()->send();
                            } catch (\Exception $e) {
                                Notification::make()->title('Gagal Mengabaikan')->body($e->getMessage())->danger()->send();
                            }
                        }),
                ]),
            ])
            ->bulkActions([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Ringkasan')
                            ->schema([
                                Section::make('Informasi Produk & Cabang')
                                    ->columns(3)
                                    ->schema([
                                        TextEntry::make('focus_number')->label('No Fokus')->weight(FontWeight::Bold),
                                        TextEntry::make('productVariant.product.name')->label('Nama Produk')->weight(FontWeight::Bold),
                                        TextEntry::make('productVariant.sku')->label('SKU / Barcode')->badge(),
                                        TextEntry::make('branch.name')->label('Cabang Terkait')->badge()->color('info'),
                                        TextEntry::make('source')
                                            ->label('Sumber')
                                            ->badge()
                                            ->color(fn (string $state) => $state === 'RULE' ? 'primary' : 'gray')
                                            ->description(fn (FocusProduct $record) => $record->rule?->name),
                                        TextEntry::make('status')
                                            ->label('Status')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'ACTIVE' => 'success',
                                                'EXPIRED' => 'warning',
                                                'RESOLVED' => 'info',
                                                'DISMISSED' => 'gray',
                                                default => 'gray',
                                            }),
                                    ]),

                                Section::make('Periode & Alasan Fokus')
                                    ->columns(2)
                                    ->schema([
                                        TextEntry::make('active_from')->label('Mulai Fokus')->dateTime('d/m/Y H:i'),
                                        TextEntry::make('active_until')->label('Aktif Sampai')->dateTime('d/m/Y H:i')->placeholder('Tanpa Batas Waktu'),
                                        TextEntry::make('reason')->label('Alasan Difokuskan')->columnSpanFull(),
                                        TextEntry::make('note')->label('Catatan Tambahan')->columnSpanFull()->placeholder('-'),
                                        TextEntry::make('focusedBy.name')->label('Ditetapkan Oleh')->placeholder('Sistem'),
                                        TextEntry::make('focused_at')->label('Waktu Penetapan')->dateTime('d/m/Y H:i'),
                                    ]),
                            ]),

                        Tabs\Tab::make('Riwayat Penjualan')
                            ->schema([
                                ViewEntry::make('sales_history')
                                    ->view('filament.focus-products.sales-tab')
                                    ->viewData(function (FocusProduct $record) {
                                        $summary = FocusProductHelper::getRecentSalesSummary($record->branch_id, $record->product_variant_id, 6);
                                        $recentSales = SaleItem::query()
                                            ->with(['sale.customer'])
                                            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                                            ->where('sales.branch_id', $record->branch_id)
                                            ->where('sale_items.product_variant_id', $record->product_variant_id)
                                            ->where('sales.status', '!=', 'cancelled')
                                            ->orderBy('sales.invoice_date', 'desc')
                                            ->take(20)
                                            ->get();

                                        return [
                                            'record' => $record,
                                            'branchName' => $record->branch?->name ?? 'Cabang',
                                            'salesSummary' => $summary,
                                            'recentSales' => $recentSales,
                                        ];
                                    }),
                            ]),

                        Tabs\Tab::make('Stok & Pergerakan')
                            ->schema([
                                ViewEntry::make('stock_movements')
                                    ->view('filament.focus-products.stock-tab')
                                    ->viewData(function (FocusProduct $record) {
                                        $branchStock = ProductBranchStock::where('branch_id', $record->branch_id)
                                            ->where('product_variant_id', $record->product_variant_id)
                                            ->first();

                                        $agingResult = app(\App\Actions\FocusProduct\CalculateStockAging::class)
                                            ->execute($record->branch_id, $record->product_variant_id);

                                        $movements = StockMovement::where('branch_id', $record->branch_id)
                                            ->where('product_variant_id', $record->product_variant_id)
                                            ->orderBy('created_at', 'desc')
                                            ->take(20)
                                            ->get();

                                        return [
                                            'record' => $record,
                                            'branchName' => $record->branch?->name ?? 'Cabang',
                                            'currentStock' => $branchStock ? (int) $branchStock->stock : 0,
                                            'hpp' => $branchStock ? (int) $branchStock->hpp : 0,
                                            'agingData' => $agingResult,
                                            'movements' => $movements,
                                        ];
                                    }),
                            ]),

                        Tabs\Tab::make('Log Aktivitas')
                            ->schema([
                                ViewEntry::make('activity_log')
                                    ->view('filament.focus-products.activity-tab')
                                    ->viewData(fn (FocusProduct $record) => ['record' => $record]),
                            ]),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFocusProducts::route('/'),
            'recommendations' => Pages\ManageRecommendations::route('/recommendations'),
            'view' => Pages\ViewFocusProduct::route('/{record}'),
        ];
    }
}
