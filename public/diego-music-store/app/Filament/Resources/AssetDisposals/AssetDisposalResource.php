<?php

namespace App\Filament\Resources\AssetDisposals;

use App\Actions\Accounting\PostAssetDisposal;
use App\Models\Account;
use App\Models\Asset;
use App\Models\AssetDisposal;
use App\Models\Branch;
use Filament\Actions\Action;
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

class AssetDisposalResource extends Resource
{
    protected static ?string $model = AssetDisposal::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedTrash;

    protected static \UnitEnum|string|null $navigationGroup = 'Akuntansi';

    protected static ?string $navigationLabel = 'Disposisi & Penghapusan Aset';

    protected static ?string $modelLabel = 'Disposisi Aset';

    protected static ?string $pluralModelLabel = 'Disposisi & Penghapusan Aset';

    protected static ?int $navigationSort = 8;

    public static function form(Schema $schema): Schema
    {
        $accounts = Account::where('is_header', false)->orderBy('code')->pluck('name', 'id');

        return $schema
            ->components([
                Section::make('Informasi Transaksi Disposisi Aset')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm'      => 3,
                        ])->schema([
                            TextInput::make('disposal_number')
                                ->label('No. Bukti Disposisi')
                                ->placeholder('Otomatis (DSP-YYYYMM-XXXX)')
                                ->disabled()
                                ->dehydrated(false),

                            DatePicker::make('disposal_date')
                                ->label('Tanggal Disposisi')
                                ->default(now())
                                ->required(),

                            Select::make('disposal_type')
                                ->label('Tipe Disposisi')
                                ->options([
                                    'sale'      => 'Penjualan Aset (Dijual ke Pihak Luar)',
                                    'write_off' => 'Penghapusan Aset (Rusak Total / Afkir / Hilang)',
                                ])
                                ->default('sale')
                                ->required()
                                ->live(),

                            Select::make('asset_id')
                                ->label('Pilih Aset Tetap')
                                ->options(
                                    Asset::where('status', 'active')
                                        ->get()
                                        ->mapWithKeys(fn ($a) => [$a->id => "{$a->asset_code} - {$a->name} (Harga Perolehan: Rp " . number_format($a->purchase_cost, 0, ',', '.') . ")"])
                                )
                                ->required()
                                ->searchable()
                                ->live()
                                ->columnSpan(['sm' => 2])
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state) {
                                        $asset = Asset::find($state);
                                        if ($asset) {
                                            $set('book_value', $asset->book_value);
                                            $set('branch_id', $asset->branch_id);
                                        }
                                    }
                                }),

                            Select::make('branch_id')
                                ->label('Cabang')
                                ->options(Branch::orderBy('name')->pluck('name', 'id'))
                                ->placeholder('Semua Cabang / Pusat')
                                ->searchable(),

                            TextInput::make('book_value')
                                ->label('Nilai Buku Aset (Rp)')
                                ->numeric()
                                ->prefix('Rp')
                                ->disabled()
                                ->dehydrated(),

                            TextInput::make('disposal_amount')
                                ->label('Harga Jual / Nilai Disposisi (Rp)')
                                ->numeric()
                                ->prefix('Rp')
                                ->default(0)
                                ->visible(fn ($get) => $get('disposal_type') === 'sale')
                                ->required(fn ($get) => $get('disposal_type') === 'sale'),

                            Select::make('account_id')
                                ->label('Akun Kas / Bank Penerima')
                                ->options($accounts)
                                ->placeholder('Pilih Akun Kas/Bank')
                                ->visible(fn ($get) => $get('disposal_type') === 'sale')
                                ->searchable(),
                        ]),

                        Textarea::make('notes')
                            ->label('Catatan Disposisi / Alasan Penghapusan')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('disposal_number')
                    ->label('No. Disposisi')
                    ->fontFamily(\Filament\Support\Enums\FontFamily::Mono)
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('disposal_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('asset.name')
                    ->label('Nama Aset Tetap')
                    ->description(fn ($record) => $record->asset?->asset_code)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('disposal_type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sale'      => 'warning',
                        'write_off' => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'sale'      => 'PENJUALAN',
                        'write_off' => 'PENGHAPUSAN (AFKIR)',
                        default     => strtoupper($state),
                    }),

                TextColumn::make('book_value')
                    ->label('Nilai Buku Saat Disposisi')
                    ->money('IDR'),

                TextColumn::make('disposal_amount')
                    ->label('Harga Jual')
                    ->money('IDR'),

                TextColumn::make('gain_loss_amount')
                    ->label('Laba / (Rugi)')
                    ->money('IDR')
                    ->color(fn ($state) => (float) $state >= 0 ? 'success' : 'danger')
                    ->weight('bold'),

                TextColumn::make('status')
                    ->label('Status Posting')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'posted' => 'success',
                        'draft'  => 'gray',
                        default  => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'posted' => 'POSTED',
                        'draft'  => 'DRAFT',
                        default  => strtoupper($state),
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                \Filament\Actions\EditAction::make()
                    ->modalWidth('4xl')
                    ->visible(fn (AssetDisposal $record) => $record->status === 'draft'),

                Action::make('post')
                    ->label('Proses Posting')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->extraAttributes([
                        'class' => '!text-white dark:!text-white',
                    ])
                    ->visible(fn (AssetDisposal $record) => $record->status === 'draft')
                    ->requiresConfirmation()
                    ->action(function (AssetDisposal $record) {
                        app(PostAssetDisposal::class)->execute($record);
                    }),

                \Filament\Actions\DeleteAction::make()
                    ->visible(fn (AssetDisposal $record) => $record->status === 'draft'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssetDisposals::route('/'),
        ];
    }
}
