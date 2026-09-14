<?php

namespace App\Filament\Resources\CommissionSchemes;

use App\Filament\Resources\CommissionSchemes\Pages\ListCommissionSchemes;
use App\Models\CommissionScheme;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class CommissionSchemeResource extends Resource
{
    protected static ?string $model = CommissionScheme::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?string $navigationLabel = 'Skema & Aturan Komisi';

    protected static ?string $pluralModelLabel = 'Skema Komisi';

    protected static ?string $modelLabel = 'Skema Komisi';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return 'Manajemen Karyawan';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('branch_id')
                    ->label('Cabang')
                    ->relationship('branch', 'name')
                    ->nullable()
                    ->placeholder('Semua Cabang'),
                Forms\Components\Select::make('employees')
                    ->label('Staf Karyawan Spesifik (Multiple Choice / Opsional)')
                    ->relationship(
                        name: 'employees',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (\Illuminate\Database\Eloquent\Builder $query, $record) => $query->whereDoesntHave('commissionSchemes', function ($q) use ($record) {
                            if ($record) {
                                $q->where('commission_schemes.id', '!=', $record->id);
                            }
                        })
                    )
                    ->multiple()
                    ->preload()
                    ->placeholder('Kosongkan untuk semua staf karyawan (Umum)'),
                Forms\Components\TextInput::make('name')
                    ->label('Nama Skema')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('calculation_type')
                    ->label('Tipe Kalkulasi')
                    ->options([
                        'percentage' => 'Persentase (%)',
                        'fixed_amount' => 'Nominal Flat (Rp)',
                    ])
                    ->required()
                    ->default('percentage'),
                Forms\Components\TextInput::make('rate')
                    ->label('Nilai / Rate')
                    ->numeric()
                    ->required()
                    ->default(2.0),
                Forms\Components\Select::make('applies_to')
                    ->label('Target Penerapan')
                    ->options([
                        'all_sales' => 'Semua Transaksi Sales',
                        'category' => 'Spesifik Kategori Penjualan',
                        'product' => 'Spesifik Produk Tertentu',
                    ])
                    ->required()
                    ->default('all_sales'),
                Forms\Components\Select::make('target_product_id')
                    ->label('Produk Target')
                    ->relationship('targetProduct', 'name')
                    ->nullable(),
                Forms\Components\Select::make('target_sale_category_id')
                    ->label('Kategori Target')
                    ->relationship('targetCategory', 'name')
                    ->nullable(),
                Forms\Components\TextInput::make('min_monthly_sales_target')
                    ->label('Minimal Omset Bulanan')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Skema')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->default('Semua Cabang'),
                Tables\Columns\TextColumn::make('calculation_type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'percentage' ? 'Persentase (%)' : 'Flat (Rp)'),
                Tables\Columns\TextColumn::make('rate')
                    ->label('Rate')
                    ->formatStateUsing(fn ($record) => $record->calculation_type === 'percentage' ? $record->rate . '%' : 'Rp ' . number_format($record->rate, 0, ',', '.')),
                Tables\Columns\TextColumn::make('applies_to')
                    ->label('Target Penerapan')
                    ->badge(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCommissionSchemes::route('/'),
        ];
    }
}
