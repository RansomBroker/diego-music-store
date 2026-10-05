<?php

namespace App\Filament\Resources;

use App\Actions\StockTransfer\ApproveStockTransfer;
use App\Actions\StockTransfer\CancelStockTransfer;
use App\Actions\StockTransfer\CompleteStockTransfer;
use App\Actions\StockTransfer\SubmitStockTransfer;
use App\Filament\Resources\StockTransferResource\Pages;
use App\Models\Branch;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\StockTransfer;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class StockTransferResource extends Resource
{
    protected static ?string $model = StockTransfer::class;

    protected static string|UnitEnum|null $navigationGroup = 'Persediaan';
    protected static ?string $modelLabel = 'Transfer Antar Cabang';
    protected static ?string $pluralModelLabel = 'Transfer Antar Cabang';
    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                Section::make('Informasi Transfer')
                    ->columns(2)
                    ->schema([
                        TextInput::make('transfer_number')
                            ->label('No Transfer')
                            ->default(fn () => StockTransfer::generateTransferNumber())
                            ->disabled()
                            ->dehydrated(false),

                        DatePicker::make('transfer_date')
                            ->label('Tanggal')
                            ->default(now())
                            ->required(),

                        Select::make('from_branch_id')
                            ->label('Cabang Asal')
                            ->options(Branch::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (Set $set) {
                                $set('to_branch_id', null);
                            }),

                        Select::make('to_branch_id')
                            ->label('Cabang Tujuan')
                            ->options(function (Get $get) {
                                $from = $get('from_branch_id');
                                $query = Branch::where('is_active', true);
                                if ($from) {
                                    $query->where('id', '!=', $from);
                                }
                                return $query->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required(),

                        Textarea::make('description')
                            ->label('Alasan / Deskripsi')
                            ->placeholder('Contoh: Pemindahan stok untuk kebutuhan display cabang Pontianak...')
                            ->columnSpanFull(),
                    ]),

                Section::make('Daftar Produk')
                    ->description('Pilih produk yang akan dipindahkan beserta kuantitas dan harga pokok (HPP)')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Select::make('product_variant_id')
                                    ->label('Produk')
                                    ->options(function () {
                                        return ProductVariant::with('product')
                                            ->where('is_active', true)
                                            ->get()
                                            ->mapWithKeys(function ($variant) {
                                                $barcode = $variant->barcode ? " [{$variant->barcode}]" : "";
                                                return [$variant->id => "{$variant->product->name} ({$variant->sku}){$barcode}"];
                                            });
                                    })
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->columnSpan(4)
                                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                        if ($state) {
                                            $variant = ProductVariant::find($state);
                                            $fromBranchId = $get('../../from_branch_id');
                                            
                                            $branchStock = null;
                                            if ($fromBranchId) {
                                                $branchStock = ProductBranchStock::where('product_variant_id', $state)
                                                    ->where('branch_id', $fromBranchId)
                                                    ->first();
                                            }

                                            $hpp = $branchStock?->hpp ?: ($variant?->hpp ?: ($variant?->cost_price ?: 0));
                                            $set('unit_cost', (float) $hpp);
                                            
                                            $qty = (int) ($get('qty') ?? 1);
                                            $set('total_cost', (float) ($hpp * $qty));
                                        }
                                    }),

                                Placeholder::make('stok_asal')
                                    ->label('Stok Asal')
                                    ->columnSpan(2)
                                    ->content(function (Get $get) {
                                        $variantId = $get('product_variant_id');
                                        $fromBranchId = $get('../../from_branch_id');
                                        
                                        if (!$variantId || !$fromBranchId) {
                                            return '-';
                                        }
                                        
                                        $variant = ProductVariant::find($variantId);
                                        if (!$variant) {
                                            return '-';
                                        }
                                        
                                        $stock = $variant->stockForBranch((int) $fromBranchId);
                                        return number_format($stock) . ' unit';
                                    }),

                                TextInput::make('qty')
                                    ->label('Qty')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->default(1)
                                    ->reactive()
                                    ->columnSpan(2)
                                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                        $unitCost = (float) ($get('unit_cost') ?? 0);
                                        $qty = (int) ($state ?? 0);
                                        $set('total_cost', $unitCost * $qty);
                                    }),

                                TextInput::make('unit_cost')
                                    ->label('HPP (Unit Cost)')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required()
                                    ->reactive()
                                    ->columnSpan(2)
                                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                        $unitCost = (float) ($state ?? 0);
                                        $qty = (int) ($get('qty') ?? 0);
                                        $set('total_cost', $unitCost * $qty);
                                    }),

                                TextInput::make('total_cost')
                                    ->label('Total Nilai')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required()
                                    ->readOnly()
                                    ->columnSpan(2),
                            ])
                            ->columns(12)
                            ->defaultItems(1)
                            ->disableItemMovement()
                            ->addActionLabel('+ Tambah Produk'),

                        Section::make('Ringkasan Transfer')
                            ->columns(2)
                            ->schema([
                                Placeholder::make('summary_total_qty')
                                    ->label('Total Kuantitas (Qty)')
                                    ->content(function (Get $get) {
                                        $items = $get('items') ?? [];
                                        $totalQty = 0;
                                        foreach ($items as $item) {
                                            $totalQty += (int) ($item['qty'] ?? 0);
                                        }
                                        return number_format($totalQty) . ' item';
                                    }),

                                Placeholder::make('summary_total_cost')
                                    ->label('Total Nilai HPP')
                                    ->content(function (Get $get) {
                                        $items = $get('items') ?? [];
                                        $totalCost = 0;
                                        foreach ($items as $item) {
                                            $qty = (int) ($item['qty'] ?? 0);
                                            $cost = (float) ($item['unit_cost'] ?? 0);
                                            $totalCost += ($qty * $cost);
                                        }
                                        return 'Rp ' . number_format($totalCost, 0, ',', '.');
                                    }),
                            ]),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transfer_number')
                    ->label('No Transfer')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                Tables\Columns\TextColumn::make('transfer_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('fromBranch.name')
                    ->label('Cabang Asal')
                    ->sortable(),

                Tables\Columns\TextColumn::make('toBranch.name')
                    ->label('Cabang Tujuan')
                    ->sortable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Total Item')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('total_qty')
                    ->label('Total Qty')
                    ->numeric()
                    ->alignRight(),

                Tables\Columns\TextColumn::make('total_cost')
                    ->label('Nilai HPP')
                    ->money('IDR')
                    ->alignRight()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'DRAFT' => 'gray',
                        'PENDING' => 'warning',
                        'APPROVED' => 'info',
                        'COMPLETED' => 'success',
                        'CANCELLED' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'DRAFT' => 'Draft',
                        'PENDING' => 'Pending',
                        'APPROVED' => 'Approved',
                        'COMPLETED' => 'Completed',
                        'CANCELLED' => 'Cancelled',
                    ]),

                Tables\Filters\SelectFilter::make('from_branch_id')
                    ->label('Cabang Asal')
                    ->relationship('fromBranch', 'name'),

                Tables\Filters\SelectFilter::make('to_branch_id')
                    ->label('Cabang Tujuan')
                    ->relationship('toBranch', 'name'),

                Tables\Filters\Filter::make('transfer_date')
                    ->label('Rentang Tanggal')
                    ->form([
                        DatePicker::make('from')->label('Dari Tanggal'),
                        DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('transfer_date', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('transfer_date', '<=', $date));
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),

                    EditAction::make()
                        ->visible(fn (StockTransfer $record): bool => in_array($record->status, ['DRAFT'])),

                    Action::make('ajukan')
                        ->label('Ajukan')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('warning')
                        ->visible(fn (StockTransfer $record): bool => $record->status === 'DRAFT')
                        ->requiresConfirmation()
                        ->modalHeading('Ajukan Transfer Barang')
                        ->modalDescription('Apakah Anda yakin ingin mengajukan transfer ini ke status PENDING?')
                        ->action(function (StockTransfer $record) {
                            try {
                                app(SubmitStockTransfer::class)->execute($record);
                                Notification::make()->title('Transfer Berhasil Diajukan')->success()->send();
                            } catch (\Exception $e) {
                                Notification::make()->title('Gagal Mengajukan')->body($e->getMessage())->danger()->send();
                            }
                        }),

                    Action::make('approve')
                        ->label('Approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('info')
                        ->visible(fn (StockTransfer $record): bool => $record->status === 'PENDING')
                        ->requiresConfirmation()
                        ->modalHeading('Setujui Transfer Barang')
                        ->modalDescription('Apakah Anda yakin ingin menyetujui transfer ini?')
                        ->action(function (StockTransfer $record) {
                            try {
                                app(ApproveStockTransfer::class)->execute($record);
                                Notification::make()->title('Transfer Berhasil Disetujui')->success()->send();
                            } catch (\Exception $e) {
                                Notification::make()->title('Gagal Menyetujui')->body($e->getMessage())->danger()->send();
                            }
                        }),

                    Action::make('proses_transfer')
                        ->label('Proses Transfer')
                        ->icon('heroicon-o-truck')
                        ->color('success')
                        ->visible(fn (StockTransfer $record): bool => $record->status === 'APPROVED')
                        ->requiresConfirmation()
                        ->modalHeading('Proses Transfer Barang')
                        ->modalDescription('Stok fisik akan dipindahkan dan jurnal akuntansi 4 sisi akan otomatis diposting. Lanjutkan?')
                        ->action(function (StockTransfer $record) {
                            try {
                                app(CompleteStockTransfer::class)->execute($record);
                                Notification::make()->title('Transfer Selesai')->body('Stok telah dipindahkan dan Jurnal Akuntansi berhasil diposting.')->success()->send();
                            } catch (\Exception $e) {
                                Notification::make()->title('Gagal Memproses')->body($e->getMessage())->danger()->send();
                            }
                        }),

                    Action::make('cancel')
                        ->label('Batalkan')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (StockTransfer $record): bool => in_array($record->status, ['DRAFT', 'PENDING']))
                        ->requiresConfirmation()
                        ->modalHeading('Batalkan Transfer')
                        ->modalDescription('Apakah Anda yakin ingin membatalkan transaksi transfer ini?')
                        ->action(function (StockTransfer $record) {
                            try {
                                app(CancelStockTransfer::class)->execute($record);
                                Notification::make()->title('Transfer Dibatalkan')->success()->send();
                            } catch (\Exception $e) {
                                Notification::make()->title('Gagal Membatalkan')->body($e->getMessage())->danger()->send();
                            }
                        }),

                    Action::make('lihat_jurnal')
                        ->label('Lihat Jurnal')
                        ->icon('heroicon-o-book-open')
                        ->url(fn (StockTransfer $record): string => \App\Filament\Resources\JournalEntries\JournalEntryResource::getUrl('index') . "?tableSearch=" . urlencode($record->journalEntry?->entry_no ?? ''))
                        ->openUrlInNewTab()
                        ->visible(fn (StockTransfer $record): bool => $record->status === 'COMPLETED' && $record->journal_entry_id !== null),

                    Action::make('lihat_pergerakan_stok')
                        ->label('Lihat Pergerakan Stok')
                        ->icon('heroicon-o-arrows-right-left')
                        ->url(fn (StockTransfer $record): string => \App\Filament\Resources\StockMovements\StockMovementResource::getUrl('index') . "?reference_type=" . urlencode(StockTransfer::class) . "&reference_id={$record->id}")
                        ->openUrlInNewTab()
                        ->visible(fn (StockTransfer $record): bool => $record->status === 'COMPLETED'),

                    DeleteAction::make()
                        ->visible(fn (StockTransfer $record): bool => in_array($record->status, ['DRAFT'])),
                ])
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
                        Tabs\Tab::make('Detail')
                            ->schema([
                                Section::make('Informasi Transfer')
                                    ->columns(2)
                                    ->schema([
                                        TextEntry::make('transfer_number')->label('No Transfer')->weight(FontWeight::Bold),
                                        TextEntry::make('transfer_date')->label('Tanggal')->date('d/m/Y'),
                                        TextEntry::make('fromBranch.name')->label('Cabang Asal'),
                                        TextEntry::make('toBranch.name')->label('Cabang Tujuan'),
                                        TextEntry::make('status')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'DRAFT' => 'gray',
                                                'PENDING' => 'warning',
                                                'APPROVED' => 'info',
                                                'COMPLETED' => 'success',
                                                'CANCELLED' => 'danger',
                                                default => 'gray',
                                            }),
                                        TextEntry::make('total_qty')->label('Total Qty')->numeric(),
                                        TextEntry::make('total_cost')->label('Total Nilai HPP')->money('IDR'),
                                        TextEntry::make('description')->label('Alasan / Deskripsi')->columnSpanFull()->placeholder('-'),
                                    ]),
                            ]),

                        Tabs\Tab::make('Barang')
                            ->schema([
                                RepeatableEntry::make('items')
                                    ->label('Daftar Produk yang Ditransfer')
                                    ->schema([
                                        TextEntry::make('productVariant.product.name')->label('Produk'),
                                        TextEntry::make('productVariant.sku')->label('SKU'),
                                        TextEntry::make('qty')->label('Qty')->numeric(),
                                        TextEntry::make('unit_cost')->label('HPP')->money('IDR'),
                                        TextEntry::make('total_cost')->label('Total Nilai')->money('IDR'),
                                    ])
                                    ->columns(5),

                                Section::make('Ringkasan')
                                    ->columns(2)
                                    ->schema([
                                        TextEntry::make('total_qty')->label('Total Qty')->numeric(),
                                        TextEntry::make('total_cost')->label('Total Nilai HPP')->money('IDR'),
                                    ]),
                            ]),

                        Tabs\Tab::make('Accounting')
                            ->schema([
                                Section::make('Informasi Jurnal Umum')
                                    ->columns(2)
                                    ->schema([
                                        TextEntry::make('journalEntry.entry_no')
                                            ->label('No Journal')
                                            ->weight(FontWeight::Bold)
                                            ->placeholder('Belum ada jurnal'),

                                        TextEntry::make('journalEntry.date')
                                            ->label('Tanggal Jurnal')
                                            ->date('d/m/Y')
                                            ->placeholder('-'),
                                    ]),

                                Section::make('Pencatatan Keuangan Sisi Cabang Asal')
                                    ->description(fn ($record) => $record?->fromBranch ? "Cabang Asal: {$record->fromBranch->name}" : '')
                                    ->schema([
                                        RepeatableEntry::make('journalEntry.items')
                                            ->label('Jurnal Cabang Asal')
                                            ->getStateUsing(function ($record) {
                                                if (!$record?->journalEntry) return [];
                                                $originAccountIds = array_filter([
                                                    $record->fromBranch?->interbranch_receivable_account_id,
                                                    $record->fromBranch?->inventory_account_id,
                                                ]);
                                                return $record->journalEntry->items()
                                                    ->whereIn('account_id', $originAccountIds)
                                                    ->with('account')
                                                    ->get();
                                            })
                                            ->schema([
                                                TextEntry::make('account.name')->label('Nama Akun'),
                                                TextEntry::make('notes')->label('Keterangan'),
                                                TextEntry::make('debit')->label('Debit')->money('IDR'),
                                                TextEntry::make('credit')->label('Credit')->money('IDR'),
                                            ])
                                            ->columns(4),
                                    ])
                                    ->visible(fn ($record) => $record?->journal_entry_id !== null),

                                Section::make('Pencatatan Keuangan Sisi Cabang Tujuan')
                                    ->description(fn ($record) => $record?->toBranch ? "Cabang Tujuan: {$record->toBranch->name}" : '')
                                    ->schema([
                                        RepeatableEntry::make('journalEntry.items')
                                            ->label('Jurnal Cabang Tujuan')
                                            ->getStateUsing(function ($record) {
                                                if (!$record?->journalEntry) return [];
                                                $destAccountIds = array_filter([
                                                    $record->toBranch?->inventory_account_id,
                                                    $record->toBranch?->interbranch_payable_account_id,
                                                ]);
                                                return $record->journalEntry->items()
                                                    ->whereIn('account_id', $destAccountIds)
                                                    ->with('account')
                                                    ->get();
                                            })
                                            ->schema([
                                                TextEntry::make('account.name')->label('Nama Akun'),
                                                TextEntry::make('notes')->label('Keterangan'),
                                                TextEntry::make('debit')->label('Debit')->money('IDR'),
                                                TextEntry::make('credit')->label('Credit')->money('IDR'),
                                            ])
                                            ->columns(4),
                                    ])
                                    ->visible(fn ($record) => $record?->journal_entry_id !== null),
                            ]),

                        Tabs\Tab::make('Activity')
                            ->schema([
                                Section::make('Riwayat Waktu Transaksi')
                                    ->columns(2)
                                    ->schema([
                                        TextEntry::make('created_at')->label('Dibuat Pada')->dateTime('d/m/Y H:i:s'),
                                        TextEntry::make('submitted_at')->label('Diajukan Pada')->dateTime('d/m/Y H:i:s')->placeholder('-'),
                                        TextEntry::make('approved_at')->label('Disetujui Pada')->dateTime('d/m/Y H:i:s')->placeholder('-'),
                                        TextEntry::make('completed_at')->label('Selesai Pada')->dateTime('d/m/Y H:i:s')->placeholder('-'),
                                        TextEntry::make('cancelled_at')->label('Dibatalkan Pada')->dateTime('d/m/Y H:i:s')->placeholder('-'),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockTransfers::route('/'),
            'create' => Pages\CreateStockTransfer::route('/create'),
            'view' => Pages\ViewStockTransfer::route('/{record}'),
            'edit' => Pages\EditStockTransfer::route('/{record}/edit'),
        ];
    }
}
