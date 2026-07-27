<?php

namespace App\Filament\Resources\Assets;

use App\Actions\Accounting\CreateAsset as CreateAssetAction;
use App\Models\Asset;
use App\Models\Branch;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssetResource extends Resource
{
    protected static ?string $model = Asset::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static \UnitEnum|string|null $navigationGroup = 'Akuntansi';

    protected static ?string $navigationLabel = 'Master Aset Tetap';

    protected static ?string $modelLabel = 'Aset Tetap';

    protected static ?string $pluralModelLabel = 'Master Aset Tetap';

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Aset Tetap')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm'      => 3,
                        ])->schema([
                            TextInput::make('asset_code')
                                ->label('Kode Aset')
                                ->placeholder('Otomatis (AST-YYYYMM-XXXX)')
                                ->disabled()
                                ->dehydrated(false),

                            TextInput::make('name')
                                ->label('Nama Aset Tetap')
                                ->required()
                                ->maxLength(255),

                            Select::make('category')
                                ->label('Kategori Aset')
                                ->options([
                                    'Peralatan Audio & Sound' => 'Peralatan Audio & Sound System',
                                    'Kendaraan Operasional'   => 'Kendaraan Operasional',
                                    'Elektronik & POS'        => 'Elektronik & Komputer POS',
                                    'Bangunan & Display'      => 'Bangunan, ETALASE & Display Toko',
                                    'Inventaris Kantor'       => 'Inventaris & Mebel Kantor',
                                ])
                                ->required()
                                ->searchable(),

                            Select::make('branch_id')
                                ->label('Lokasi Cabang')
                                ->options(Branch::orderBy('name')->pluck('name', 'id'))
                                ->placeholder('Semua Cabang / Kantor Pusat')
                                ->searchable(),

                            DatePicker::make('purchase_date')
                                ->label('Tanggal Perolehan')
                                ->default(now())
                                ->required(),

                            TextInput::make('purchase_cost')
                                ->label('Harga Perolehan (Rp)')
                                ->numeric()
                                ->prefix('Rp')
                                ->required(),

                            TextInput::make('salvage_value')
                                ->label('Nilai Residu / Sisa (Rp)')
                                ->numeric()
                                ->prefix('Rp')
                                ->default(0),

                            TextInput::make('useful_life_years')
                                ->label('Masa Manfaat (Tahun)')
                                ->numeric()
                                ->default(5)
                                ->required(),

                            TextInput::make('accumulated_depreciation')
                                ->label('Akumulasi Penyusutan (Rp)')
                                ->numeric()
                                ->prefix('Rp')
                                ->default(0),
                        ]),

                        Textarea::make('notes')
                            ->label('Catatan / Spesifikasi Aset')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('asset_code')
                    ->label('Kode Aset')
                    ->fontFamily(\Filament\Support\Enums\FontFamily::Mono)
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nama Aset')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('category')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->placeholder('Pusat'),

                TextColumn::make('purchase_date')
                    ->label('Tgl Perolehan')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('purchase_cost')
                    ->label('Harga Perolehan')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('accumulated_depreciation')
                    ->label('Akum. Penyusutan')
                    ->money('IDR'),

                TextColumn::make('book_value')
                    ->label('Nilai Buku')
                    ->money('IDR')
                    ->weight('bold'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'     => 'success',
                        'disposed'   => 'warning',
                        'written_off' => 'danger',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active'     => 'AKTIF',
                        'disposed'   => 'TERJUAL',
                        'written_off' => 'DIHAPUS (AFKIR)',
                        default      => strtoupper($state),
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                \Filament\Actions\EditAction::make()
                    ->modalWidth('4xl'),
                \Filament\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssets::route('/'),
        ];
    }
}
