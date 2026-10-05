<?php

namespace App\Filament\Resources\FocusProductResource\Pages;

use App\Actions\FocusProduct\AcceptFocusRecommendation;
use App\Actions\FocusProduct\DismissRecommendation;
use App\Actions\FocusProduct\EvaluateFocusRules;
use App\Filament\Resources\FocusProductResource;
use App\Models\FocusProductRecommendation;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ManageRecommendations extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = FocusProductResource::class;

    protected static ?string $title = 'Rekomendasi Produk Fokus';

    protected string $view = 'filament.focus-products.recommendations-page';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('run_evaluation')
                ->label('Jalankan Evaluasi Rule')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->action(function () {
                    try {
                        $result = app(EvaluateFocusRules::class)->execute();
                        Notification::make()
                            ->title('Evaluasi Selesai')
                            ->body("Ditemukan {$result['recommendations_generated']} rekomendasi baru dan {$result['recommendations_updated']} rekomendasi diperbarui.")
                            ->success()
                            ->send();
                        $this->resetTable();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Evaluasi Gagal')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('back')
                ->label('Kembali ke Produk Fokus')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn () => FocusProductResource::getUrl('index')),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                FocusProductRecommendation::query()
                    ->with(['branch', 'productVariant.product', 'rule'])
                    ->where('status', 'PENDING')
            )
            ->defaultSort('score', 'desc')
            ->columns([
                ImageColumn::make('productVariant.product.image_path')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl('/images/default-product.png'),

                TextColumn::make('productVariant.product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->description(fn (FocusProductRecommendation $record) => $record->productVariant?->name !== 'Standard' ? $record->productVariant?->name : null),

                TextColumn::make('productVariant.sku')
                    ->label('SKU')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('rule.name')
                    ->label('Aturan (Rule)')
                    ->badge()
                    ->color(fn (FocusProductRecommendation $record): string => match ($record->rule?->code) {
                        'dead_stock' => 'danger',
                        'slow_moving' => 'warning',
                        'aging_stock' => 'info',
                        default => 'primary',
                    }),

                TextColumn::make('current_stock')
                    ->label('Stok')
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn ($state) => "{$state} unit"),

                TextColumn::make('recent_sales_qty')
                    ->label('Penjualan (6 Bln)')
                    ->formatStateUsing(fn ($state) => "{$state} unit"),

                TextColumn::make('aging_days')
                    ->label('Umur Stok')
                    ->formatStateUsing(fn ($state) => $state ? "{$state} hari" : '-'),

                TextColumn::make('score')
                    ->label('Skor Prioritas')
                    ->badge()
                    ->sortable()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 85 => 'danger',
                        $state >= 70 => 'warning',
                        default => 'primary',
                    }),

                TextColumn::make('reason')
                    ->label('Alasan Rekomendasi')
                    ->wrap(),
            ])
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('Cabang')
                    ->relationship('branch', 'name'),

                SelectFilter::make('rule_id')
                    ->label('Aturan Rule')
                    ->relationship('rule', 'name'),
            ])
            ->actions([
                Action::make('accept')
                    ->label('Tambahkan ke Fokus')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->modalHeading('Tambahkan ke Produk Fokus')
                    ->modalDescription('Tentukan periode batas aktif dan catatan untuk produk ini.')
                    ->form([
                        DateTimePicker::make('active_until')
                            ->label('Aktif Sampai')
                            ->helperText('Kosongkan jika ingin dibuat tanpa batas waktu.'),

                        Textarea::make('note')
                            ->label('Catatan Strategi')
                            ->placeholder('Contoh: Adakan diskon 15% atau tawarkan sebagai bundling'),
                    ])
                    ->action(function (FocusProductRecommendation $record, array $data) {
                        try {
                            app(AcceptFocusRecommendation::class)->execute(
                                $record->id,
                                $data['active_until'] ?? null,
                                $data['note'] ?? null,
                                auth()->id()
                            );
                            Notification::make()
                                ->title('Produk Berhasil Ditambahkan ke Fokus')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal Menambahkan')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('dismiss')
                    ->label('Abaikan')
                    ->icon('heroicon-o-x-mark')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Abaikan Rekomendasi')
                    ->modalDescription('Apakah Anda yakin ingin mengabaikan rekomendasi untuk produk ini?')
                    ->action(function (FocusProductRecommendation $record) {
                        try {
                            app(DismissRecommendation::class)->execute($record->id, auth()->id());
                            Notification::make()
                                ->title('Rekomendasi Diabaikan')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal Mengabaikan')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                BulkAction::make('accept_bulk')
                    ->label('Tambahkan Terpilih ke Fokus')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->modalHeading('Tambahkan Produk Terpilih ke Fokus')
                    ->form([
                        DateTimePicker::make('active_until')
                            ->label('Aktif Sampai (Semua Produk Terpilih)')
                            ->helperText('Batas waktu periode aktif untuk seluruh produk terpilih.'),

                        Textarea::make('note')
                            ->label('Catatan Bersama')
                            ->placeholder('Catatan atau instruksi serentak'),
                    ])
                    ->action(function (Collection $records, array $data) {
                        $count = 0;
                        $action = app(AcceptFocusRecommendation::class);
                        foreach ($records as $record) {
                            try {
                                $action->execute(
                                    $record->id,
                                    $data['active_until'] ?? null,
                                    $data['note'] ?? null,
                                    auth()->id()
                                );
                                $count++;
                            } catch (\Exception $e) {
                                // Lewati jika duplikat
                            }
                        }

                        Notification::make()
                            ->title("{$count} Produk Berhasil Ditambahkan ke Fokus")
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);
    }
}
