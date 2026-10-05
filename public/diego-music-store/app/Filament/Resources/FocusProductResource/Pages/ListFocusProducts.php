<?php

namespace App\Filament\Resources\FocusProductResource\Pages;

use App\Actions\FocusProduct\CreateManualFocusProduct;
use App\Actions\FocusProduct\EvaluateFocusRules;
use App\Filament\Resources\FocusProductResource;
use App\Models\Branch;
use App\Models\FocusProduct;
use App\Models\FocusProductRecommendation;
use App\Models\ProductVariant;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Builder;

class ListFocusProducts extends ListRecords
{
    protected static string $resource = FocusProductResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->badge(fn () => FocusProduct::count()),

            'active' => Tab::make('Aktif')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'ACTIVE'))
                ->badge(fn () => FocusProduct::where('status', 'ACTIVE')->count())
                ->badgeColor('success'),

            'expired' => Tab::make('Kadaluarsa')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'EXPIRED'))
                ->badge(fn () => FocusProduct::where('status', 'EXPIRED')->count())
                ->badgeColor('warning'),

            'resolved' => Tab::make('Selesai')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'RESOLVED'))
                ->badge(fn () => FocusProduct::where('status', 'RESOLVED')->count())
                ->badgeColor('info'),

            'dismissed' => Tab::make('Diabaikan')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'DISMISSED'))
                ->badge(fn () => FocusProduct::where('status', 'DISMISSED')->count())
                ->badgeColor('gray'),
        ];
    }

    protected function getHeaderActions(): array
    {
        $pendingCount = FocusProductRecommendation::where('status', 'PENDING')->count();

        return [
            Action::make('create_manual')
                ->label('+ Tambah Produk Fokus')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->modalHeading('Tambah Produk Fokus (Manual)')
                ->form([
                    Select::make('method')
                        ->label('Metode Penentuan Fokus *')
                        ->options([
                            'rule' => 'Gunakan Aturan',
                            'manual' => 'Pilih Produk Manual',
                        ])
                        ->default('rule')
                        ->required()
                        ->live(),

                    Select::make('branch_id')
                        ->label('Cabang *')
                        ->options(Branch::where('is_active', true)->pluck('name', 'id'))
                        ->required()
                        ->searchable()
                        ->preload()
                        ->live()
                        ->default(fn () => \App\Helpers\BranchHelper::getActiveBranchId() ?: Branch::where('is_active', true)->value('id')),

                    // 1. OPSI: GUNAKAN ATURAN (Varian tidak perlu dipilih, cukup centang aturan)
                    \Filament\Forms\Components\CheckboxList::make('rules')
                        ->label('Pilih Aturan Fokus *')
                        ->options([
                            'dead_stock' => 'Dead Stock',
                            'slow_moving' => 'Slow Moving',
                            'aging_stock' => 'Aging Stock',
                            'overstock' => 'Overstock',
                            'stock_value_at_risk' => 'Stock Value at Risk',
                        ])
                        ->descriptions([
                            'dead_stock' => 'Stok > 0, penjualan 0 selama 6 bulan • 🔴 Tinggi',
                            'slow_moving' => 'Penjualan < 3 unit / 6 bulan • 🔴 Tinggi',
                            'aging_stock' => 'Stok tertua > 180 hari • 🔴 Tinggi',
                            'overstock' => 'Stok jauh di atas kebutuhan berdasarkan velocity • 🔴 Tinggi',
                            'stock_value_at_risk' => 'Nilai HPP stok tinggi + velocity rendah • 🔴 Tinggi',
                        ])
                        ->columns(1)
                        ->visible(fn (Get $get) => ($get('method') ?? 'rule') === 'rule')
                        ->required(fn (Get $get) => ($get('method') ?? 'rule') === 'rule')
                        ->helperText('Sistem akan otomatis mengevaluasi stok cabang dan memfokuskan seluruh produk yang memenuhi aturan terpilih.'),

                    // 2. OPSI: PILIH PRODUK MANUAL (Tanpa checkbox kategori/alasan aturan)
                    Select::make('product_variant_id')
                        ->label('Produk / Varian *')
                        ->placeholder('Pilih produk yang ada di cabang ini')
                        ->options(function (Get $get) {
                            $branchId = $get('branch_id');
                            if (!$branchId) {
                                return [];
                            }
                            return ProductVariant::with('product')
                                ->whereHas('branchStocks', function ($q) use ($branchId) {
                                    $q->where('branch_id', $branchId)->where('stock', '>', 0);
                                })
                                ->get()
                                ->mapWithKeys(fn ($v) => [
                                    $v->id => "{$v->sku} - {$v->product->name}" . ($v->name !== 'Standard' ? " ({$v->name})" : '')
                                ]);
                        })
                        ->searchable()
                        ->preload()
                        ->visible(fn (Get $get) => $get('method') === 'manual')
                        ->required(fn (Get $get) => $get('method') === 'manual'),

                    TextInput::make('reason')
                        ->label('Alasan Fokus *')
                        ->placeholder('Contoh: Produk perlu didorong penjualannya melalui promosi')
                        ->visible(fn (Get $get) => $get('method') === 'manual')
                        ->required(fn (Get $get) => $get('method') === 'manual'),

                    DateTimePicker::make('active_until')
                        ->label('Aktif Sampai')
                        ->helperText('Kosongkan jika ingin dibuat tanpa batas waktu.'),

                    Textarea::make('note')
                        ->label('Catatan Tambahan')
                        ->rows(2)
                        ->placeholder('Catatan internal, strategi diskon, atau instruksi tim kasir/sales'),
                ])
                ->action(function (array $data) {
                    try {
                        $method = $data['method'] ?? 'rule';

                        if ($method === 'rule') {
                            $result = app(\App\Actions\FocusProduct\ApplyFocusRulesToBranch::class)->execute(
                                (int) $data['branch_id'],
                                $data['rules'] ?? [],
                                $data['active_until'] ?? null,
                                $data['note'] ?? null,
                                auth()->id()
                            );

                            if ($result['focused_count'] > 0) {
                                Notification::make()
                                    ->title('Aturan Fokus Berhasil Diterapkan')
                                    ->body("Berhasil memfokuskan {$result['focused_count']} produk di cabang ini.")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Tidak Ada Produk Memenuhi Kriteria')
                                    ->body('Tidak ditemukan produk di cabang ini yang sesuai dengan aturan terpilih.')
                                    ->warning()
                                    ->send();
                            }
                        } else {
                            $data['user_id'] = auth()->id();
                            $focus = app(CreateManualFocusProduct::class)->execute($data);
                            Notification::make()
                                ->title('Produk Fokus Berhasil Dibuat')
                                ->body("No Fokus: {$focus->focus_number}")
                                ->success()
                                ->send();
                        }
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal Memproses')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('recommendations')
                ->label('Rekomendasi Sistem')
                ->icon('heroicon-o-sparkles')
                ->color('warning')
                ->badge($pendingCount > 0 ? (string) $pendingCount : null)
                ->badgeColor('danger')
                ->url(fn () => FocusProductResource::getUrl('recommendations')),

            Action::make('run_evaluation')
                ->label('Jalankan Evaluasi')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    try {
                        $result = app(EvaluateFocusRules::class)->execute();
                        Notification::make()
                            ->title('Evaluasi Selesai')
                            ->body("Ditemukan {$result['recommendations_generated']} rekomendasi baru dan {$result['recommendations_updated']} rekomendasi diperbarui.")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Evaluasi Gagal')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
