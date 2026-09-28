<?php

namespace App\Filament\Resources\PurchaseOrders\Tables;

use App\Actions\Procurement\UpdatePurchaseOrder as UpdatePurchaseOrderAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('po_number')
                    ->label('Nomor PO')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('order_date')
                    ->label('Tanggal Order')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'approved' => 'info',
                        'closed' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                TextColumn::make('grand_total')
                    ->label('Grand Total')
                    ->money('idr')
                    ->sortable(),

                TextColumn::make('dp_amount')
                    ->label('Uang Muka (DP)')
                    ->money('idr')
                    ->badge()
                    ->color(fn ($state): string => intval($state) > 0 ? 'success' : 'gray')
                    ->description(fn (Model $record): ?string => $record->dp_amount > 0 ? ($record->dpAccount?->name ?? 'Terbayar') : null)
                    ->sortable(),
            ])
            ->actions([
                EditAction::make()
                    ->modalWidth('7xl')
                    ->visible(fn (Model $record) => $record->status === 'draft')
                    ->mutateRecordDataUsing(function (Model $record, array $data): array {
                        $data['discount_type'] = $record->discount_type ?? 'fixed';
                        $data['discount_value'] = $record->discount_value ?? 0;
                        $data['items'] = [];
                        foreach ($record->items as $item) {
                            $data['items'][] = [
                                'product_variant_id' => $item->product_variant_id,
                                'quantity' => $item->quantity,
                                'price' => $item->price,
                                'discount_type' => $item->discount_type ?? 'fixed',
                                'discount_value' => $item->discount_value ?? 0,
                                'tax_rate' => $item->tax_rate,
                                'notes' => $item->notes,
                                'unit_id' => $item->unit_id,
                            ];
                        }
                        return $data;
                    })
                    ->using(fn (Model $record, array $data): Model => app(UpdatePurchaseOrderAction::class)->execute($record, $data)),

                ViewAction::make()
                    ->modalWidth('7xl')
                    ->visible(fn (Model $record) => $record->status !== 'draft')
                    ->mutateRecordDataUsing(function (Model $record, array $data): array {
                        $data['discount_type'] = $record->discount_type ?? 'fixed';
                        $data['discount_value'] = $record->discount_value ?? 0;
                        $data['items'] = [];
                        foreach ($record->items as $item) {
                            $data['items'][] = [
                                'product_variant_id' => $item->product_variant_id,
                                'quantity' => $item->quantity,
                                'price' => $item->price,
                                'discount_type' => $item->discount_type ?? 'fixed',
                                'discount_value' => $item->discount_value ?? 0,
                                'tax_rate' => $item->tax_rate,
                                'notes' => $item->notes,
                                'unit_id' => $item->unit_id,
                            ];
                        }
                        return $data;
                    }),

                Action::make('payDp')
                    ->label('Bayar DP')
                    ->icon('heroicon-o-banknotes')
                    ->color('warning')
                    ->visible(fn (Model $record) => $record->status === 'approved' && ($record->dp_amount ?? 0) == 0)
                    ->modalHeading(fn (Model $record) => "Setor Uang Muka (DP) - PO #{$record->po_number}")
                    ->modalDescription(fn (Model $record) => "Grand Total PO: Rp " . number_format($record->grand_total, 0, ',', '.') . " ke " . ($record->supplier?->name ?? 'Supplier'))
                    ->modalWidth('lg')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('dp_amount')
                            ->label('Nominal Uang Muka (DP)')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->minValue(1)
                            ->default(fn (Model $record) => (int) round($record->grand_total * 0.3))
                            ->helperText(fn (Model $record) => 'Maksimal: Rp ' . number_format($record->grand_total, 0, ',', '.')),

                        \Filament\Forms\Components\Select::make('dp_account_id')
                            ->label('Rekening Kas / Bank')
                            ->options(fn () => \App\Models\Account::where('classification', 'asset')
                                ->where('is_header', false)
                                ->where(function ($q) {
                                    $q->where('code', 'like', '1-1%')
                                      ->orWhere('code', 'like', '1111%')
                                      ->orWhere('code', 'like', '1112%');
                                })
                                ->pluck('name', 'id')
                            )
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('Rekening asal penarikan dana transfer.'),

                        \Filament\Forms\Components\DatePicker::make('dp_paid_at')
                            ->label('Tanggal Transfer')
                            ->default(now())
                            ->required(),

                        \Filament\Forms\Components\TextInput::make('dp_reference_no')
                            ->label('No. Bukti / Referensi Transfer')
                            ->placeholder('Contoh: TRF-BCA-12345'),

                        \Filament\Forms\Components\Textarea::make('dp_notes')
                            ->label('Catatan')
                            ->placeholder('Catatan opsional pembayaran DP')
                            ->rows(2),
                    ])
                    ->action(function (Model $record, array $data) {
                        app(\App\Actions\Procurement\PayPurchaseOrderDownPayment::class)->execute(
                            $record,
                            $data,
                            auth()->id()
                        );

                        \Filament\Notifications\Notification::make()
                            ->title('Uang Muka Berhasil Dibayar')
                            ->body("DP sebesar Rp " . number_format($data['dp_amount'], 0, ',', '.') . " untuk PO #{$record->po_number} telah dicatat dan jurnal otomatis diterbitkan.")
                            ->success()
                            ->send();
                    }),

                Action::make('viewDp')
                    ->label('Rincian DP')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Model $record) => ($record->dp_amount ?? 0) > 0)
                    ->modalHeading(fn (Model $record) => "Rincian Uang Muka (DP) - PO #{$record->po_number}")
                    ->modalWidth('md')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(fn (Model $record) => view('backoffice.purchase-orders.dp-detail-modal', ['record' => $record])),

                Action::make('print')
                    ->label('Cetak')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn ($record) => route('backoffice.purchase-orders.print', $record))
                    ->openUrlInNewTab(),

                DeleteAction::make()
                    ->visible(fn (Model $record) => $record->status === 'draft'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
