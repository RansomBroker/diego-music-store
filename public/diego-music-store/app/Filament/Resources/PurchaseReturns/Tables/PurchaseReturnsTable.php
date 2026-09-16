<?php

namespace App\Filament\Resources\PurchaseReturns\Tables;

use App\Actions\Purchases\DeletePurchaseReturn;
use App\Actions\Purchases\PostPurchaseReturn;
use App\Actions\Purchases\UpdatePurchaseReturn;
use App\Models\Account;
use App\Models\PurchaseReturn;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PurchaseReturnsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('return_no')
                    ->label('No. Retur')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('purchaseTransaction.transaction_no')
                    ->label('No. Transaksi Pembelian')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('return_date')
                    ->label('Tanggal Retur')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Total Retur / Refund')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                TextColumn::make('return_type')
                    ->label('Metode Penyelesaian')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'invoice_deduction' => 'warning',
                        'refund'            => 'info',
                        'replacement'       => 'success',
                        'supplier_credit'   => 'primary',
                        default             => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'invoice_deduction' => 'Potong Faktur',
                        'refund'            => 'Refund Kas/Bank',
                        'replacement'       => 'Tukar Guling',
                        'supplier_credit'   => 'Deposit Supplier',
                        default             => ucfirst($state ?? '-'),
                    })
                    ->description(fn ($record): ?string => $record->return_type === 'refund' && $record->refundAccount ? "Ke: {$record->refundAccount->name}" : null),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'posted' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'posted' => 'Posted (Selesai)',
                        default => ucfirst($state),
                    }),

                TextColumn::make('reason')
                    ->label('Alasan Retur')
                    ->limit(40)
                    ->searchable(),

                TextColumn::make('creator.name')
                    ->label('Diproses Oleh')
                    ->searchable(),
            ])
            ->actions([
                ViewAction::make()
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalWidth('4xl')
                    ->modalHeading(fn (PurchaseReturn $record) => "Detail Retur Pembelian: {$record->return_no}")
                    ->modalContent(fn (PurchaseReturn $record) => view('filament.components.purchase-return-detail', [
                        'record' => $record->loadMissing(['items.productVariant.product', 'purchaseTransaction', 'supplier', 'branch', 'refundAccount', 'creator']),
                    ])),

                Action::make('post')
                    ->label('Posting')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'draft')
                    ->requiresConfirmation()
                    ->modalHeading('Posting Retur Pembelian')
                    ->modalDescription('Apakah Anda yakin ingin memposting retur ini? Stok persediaan akan otomatis berkurang dan jurnal retur akan dicatat.')
                    ->action(function (PurchaseReturn $record) {
                        app(PostPurchaseReturn::class)->execute($record);
                        Notification::make()
                            ->title('Retur Pembelian Berhasil Diposting')
                            ->success()
                            ->send();
                    }),

                EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->visible(fn ($record) => $record->status === 'draft')
                    ->modalHeading(fn (PurchaseReturn $record) => "Edit Draft Retur Pembelian: {$record->return_no}")
                    ->modalWidth('3xl')
                    ->mutateRecordDataUsing(function (PurchaseReturn $record, array $data): array {
                        $data['purchase_transaction_id'] = $record->purchase_transaction_id;
                        $data['return_items'] = $record->items->pluck('quantity', 'purchase_transaction_detail_id')->toArray();
                        $data['status'] = $record->status;
                        return $data;
                    })
                    ->form([
                        Hidden::make('purchase_transaction_id'),

                        ViewField::make('return_items')
                            ->view('filament.components.purchase-return-partial-form')
                            ->default([])
                            ->dehydrated(true),

                        Select::make('return_type')
                            ->label('Metode Penyelesaian Retur')
                            ->options([
                                'invoice_deduction' => 'Penyesuaian Faktur (Potong Hutang Tempo)',
                                'refund'            => 'Refund Dana (Pengembalian Kas / Rekening Bank)',
                                'replacement'       => 'Tukar Guling (Penggantian Barang Baru)',
                                'supplier_credit'   => 'Saldo Deposit Supplier (Kredit untuk Belanja Nanti)',
                            ])
                            ->required()
                            ->live()
                            ->helperText(fn($state) => match ($state) {
                                'invoice_deduction' => 'Mengurangi sisa tagihan hutang faktur pembelian kredit ini secara otomatis.',
                                'refund'            => 'Supplier mengembalikan dana secara tunai/transfer ke akun kas/bank toko Anda.',
                                'replacement'       => 'Barang cacat diganti dengan unit baru yang sama (kuantitas dan nilai stok seimbang, tanpa perubahan kas/hutang).',
                                'supplier_credit'   => 'Nominal retur dicatat sebagai saldo kredit nota / deposit di supplier untuk memotong pesanan berikutnya.',
                                default             => '',
                            }),

                        Select::make('refund_account_id')
                            ->label('Pilih Akun Kas / Bank Penerima Dana Refund')
                            ->placeholder('-- Pilih Akun Kas atau Rekening Bank --')
                            ->options(function () {
                                $accounts = Account::where('is_header', false)
                                    ->where('is_active', true)
                                    ->where('classification', 'asset')
                                    ->where(function ($sub) {
                                        $sub->where('name', 'like', '%kas%')
                                            ->orWhere('name', 'like', '%bank%');
                                    })
                                    ->orderBy('code', 'asc')
                                    ->pluck('name', 'id')
                                    ->toArray();

                                if (empty($accounts)) {
                                    $accounts = Account::where('is_header', false)
                                        ->where('is_active', true)
                                        ->where('classification', 'asset')
                                        ->orderBy('code', 'asc')
                                        ->pluck('name', 'id')
                                        ->toArray();
                                }
                                return $accounts;
                            })
                            ->required(fn($get) => $get('return_type') === 'refund')
                            ->visible(fn($get) => $get('return_type') === 'refund')
                            ->searchable()
                            ->preload(),

                        Select::make('replacement_status')
                            ->label('Status Penerimaan Barang Pengganti')
                            ->options([
                                'received' => 'Langsung Diterima (Barang baru pengganti langsung masuk stok)',
                                'pending'  => 'Menunggu Pengiriman (Barang pengganti belum tiba dari supplier)',
                            ])
                            ->default('received')
                            ->required(fn($get) => $get('return_type') === 'replacement')
                            ->visible(fn($get) => $get('return_type') === 'replacement'),

                        Textarea::make('reason')
                            ->label('Alasan Retur ke Supplier / Catatan')
                            ->required(),

                        Select::make('status')
                            ->label('Status Retur')
                            ->options([
                                'draft'  => 'Draft (Tetap Simpan sebagai Draft)',
                                'posted' => 'Posting (Posting Sekarang & Update Stok/Jurnal)',
                            ])
                            ->default('draft')
                            ->required(),
                    ])
                    ->using(function (PurchaseReturn $record, array $data, $livewire = null): PurchaseReturn {
                        $rawItems = $data['return_items'] 
                            ?? data_get($livewire, 'mountedActions.0.data.return_items')
                            ?? data_get($livewire, 'mountedActionData.return_items')
                            ?? data_get($livewire, 'mountedActionsData.0.return_items')
                            ?? request()->input('return_items', []);

                        $data['return_items'] = $rawItems;
                        return app(UpdatePurchaseReturn::class)->execute($record, $data);
                    })
                    ->successNotificationTitle('Draft Retur Pembelian berhasil diperbarui'),

                DeleteAction::make()
                    ->label('Hapus')
                    ->visible(fn ($record) => $record->status === 'draft')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Draft Retur Pembelian')
                    ->modalDescription('Apakah Anda yakin ingin menghapus draft retur ini? Data retur beserta item barang di dalamnya akan dihapus.')
                    ->using(fn (PurchaseReturn $record) => app(DeletePurchaseReturn::class)->execute($record))
                    ->successNotificationTitle('Draft Retur Pembelian berhasil dihapus'),
            ])
            ->recordAction('view')
            ->defaultSort('id', 'desc');
    }
}
