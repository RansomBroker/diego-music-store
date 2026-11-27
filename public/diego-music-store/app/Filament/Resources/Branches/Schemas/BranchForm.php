<?php

namespace App\Filament\Resources\Branches\Schemas;

use App\Actions\Branch\RegenerateBranchProductSkus;
use App\Filament\Resources\Branches\BranchResource;
use App\Models\Branch;
use App\Models\User;
use App\Models\Account;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Wizard\Step;

class BranchForm
{
    /**
     * Get the wizard steps for Branch creation/edit.
     *
     * @return array<Step>
     */
    public static function getWizardSteps(): array
    {
        return [
            Step::make('Informasi Umum & Manajer')
                ->description('Nama cabang, manajer penanggung jawab, logo & kontak')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->label('Nama Cabang')
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            if (empty($get('store_name')) || $get('store_name') === $state) {
                                $set('store_name', $state);
                            }
                        }),

                    TextInput::make('store_name')
                        ->maxLength(255)
                        ->label('Nama Toko / Outlet')
                        ->helperText('Otomatis sama dengan Nama Cabang jika dikosongkan'),

                    Select::make('manager_id')
                        ->label('Kepala / Manajer Cabang')
                        ->options(User::where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                        ->placeholder('Pilih Manajer Cabang')
                        ->searchable(),

                    TextInput::make('phone')
                        ->tel()
                        ->maxLength(50)
                        ->label('No. Telepon / WhatsApp'),

                    TextInput::make('email')
                        ->email()
                        ->maxLength(255)
                        ->label('Email Cabang'),

                    FileUpload::make('logo_path')
                        ->image()
                        ->disk('public')
                        ->visibility('public')
                        ->directory('branch-logos')
                        ->label('Logo Toko / Struk')
                        ->columnSpanFull(),
                ]),

            Step::make('Lokasi, Pajak & Rekening Bank')
                ->description('Alamat fisik, Kota, NPWP, dan rekening bank cabang')
                ->schema([
                    Textarea::make('address')
                        ->rows(2)
                        ->maxLength(500)
                        ->label('Alamat Lengkap Cabang')
                        ->columnSpanFull(),

                    TextInput::make('city')
                        ->maxLength(100)
                        ->label('Kota / Kabupaten'),

                    TextInput::make('province')
                        ->maxLength(100)
                        ->label('Provinsi'),

                    TextInput::make('postal_code')
                        ->maxLength(20)
                        ->label('Kode Pos'),

                    TextInput::make('npwp')
                        ->maxLength(50)
                        ->label('NPWP Cabang / Toko'),

                    Textarea::make('bank_info')
                        ->rows(2)
                        ->maxLength(500)
                        ->label('Rekening Bank Resmi Cabang')
                        ->placeholder('Contoh: BCA 1234567890 a.n. Diego Music Store')
                        ->columnSpanFull(),

                    TextInput::make('shift_start_time')
                        ->required()
                        ->default('09:00')
                        ->label('Jam Masuk Shift Default (HH:MM)')
                        ->placeholder('09:00')
                        ->helperText('Jam mulai kerja default jika hari tidak diatur secara khusus di jadwal per hari.'),

                    TextInput::make('shift_end_time')
                        ->required()
                        ->default('17:00')
                        ->label('Jam Pulang Shift Default (HH:MM)')
                        ->placeholder('17:00')
                        ->helperText('Jam selesai kerja default jika hari tidak diatur secara khusus di jadwal per hari.'),

                    \Filament\Forms\Components\Repeater::make('shift_schedules')
                        ->label('Jadwal Jam Shift per Hari')
                        ->helperText('Tambahkan jadwal khusus per hari (misal: Senin–Jumat 09:00–17:00, Sabtu–Minggu 10:00–19:00).')
                        ->schema([
                            Select::make('days')
                                ->label('Hari Kerja')
                                ->options(\App\Models\Branch::DAY_LABELS)
                                ->multiple()
                                ->required()
                                ->searchable(false)
                                ->columnSpan(2),

                            TextInput::make('start_time')
                                ->label('Jam Masuk (HH:MM)')
                                ->placeholder('09:00')
                                ->default('09:00')
                                ->required(),

                            TextInput::make('end_time')
                                ->label('Jam Pulang (HH:MM)')
                                ->placeholder('17:00')
                                ->default('17:00')
                                ->required(),
                        ])
                        ->columns(4)
                        ->addActionLabel('Tambah Jadwal Shift Hari')
                        ->default([])
                        ->columnSpanFull(),

                    Toggle::make('is_active')
                        ->label('Status Aktif Operasional')
                        ->default(true),
                ]),

            Step::make('Alokasi Staf')
                ->description('Alokasi pengguna/staf yang memiliki akses operasional ke cabang ini')
                ->schema([
                    Select::make('users')
                        ->relationship('users', 'name')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->label('Alokasi Staf & Kasir Cabang')
                        ->helperText('Pilih staf/kasir yang diberikan akses ke cabang ini')
                        ->columnSpanFull(),
                ]),

            Step::make('Akun Akuntansi & COA')
                ->description('Pengaturan Akun COA untuk Persediaan dan Transfer Antar Cabang')
                ->schema([
                    Select::make('inventory_account_id')
                        ->label('Akun Persediaan Cabang')
                        ->helperText('Akun aset untuk persediaan barang dagang khusus cabang ini')
                        ->options(fn () => Account::where('classification', 'asset')->where('is_header', false)->orderBy('code')->get()->mapWithKeys(fn ($a) => [$a->id => "{$a->code} - {$a->name}"]))
                        ->searchable()
                        ->preload()
                        ->placeholder('Pilih Akun Persediaan'),

                    Select::make('interbranch_receivable_account_id')
                        ->label('Akun Piutang Antar Cabang')
                        ->helperText('Akun aset piutang saat cabang ini mentransfer barang keluar ke cabang lain')
                        ->options(fn () => Account::where('classification', 'asset')->where('is_header', false)->orderBy('code')->get()->mapWithKeys(fn ($a) => [$a->id => "{$a->code} - {$a->name}"]))
                        ->searchable()
                        ->preload()
                        ->placeholder('Pilih Akun Piutang Antar Cabang'),

                    Select::make('interbranch_payable_account_id')
                        ->label('Akun Hutang Antar Cabang')
                        ->helperText('Akun kewajiban hutang saat cabang ini menerima transfer barang dari cabang lain')
                        ->options(fn () => Account::where('classification', 'liability')->where('is_header', false)->orderBy('code')->get()->mapWithKeys(fn ($a) => [$a->id => "{$a->code} - {$a->name}"]))
                        ->searchable()
                        ->preload()
                        ->placeholder('Pilih Akun Hutang Antar Cabang'),
                ]),

            Step::make('Prefix Kode & Penomoran')
                ->description('Prefix kode produk / SKU dan kode penomoran jurnal cabang')
                ->schema([
                    TextInput::make('sku_prefix')
                        ->label('Prefix Kode Produk / SKU')
                        ->default('DG')
                        ->placeholder('Contoh: DG, DG-SKW, DG-KTP')
                        ->helperText('Prefix otomatis untuk penomoran SKU saat tambah produk atau import produk di cabang ini (misal: DMS-P -> DMS-P-0001, DMS-P-0002)')
                        ->required(fn ($get): bool => (bool) $get('regenerate_skus_on_save'))
                        ->maxLength(20)
                        ->suffixAction(
                            Action::make('generate_skus_now')
                                ->label('Generate SKU')
                                ->icon('heroicon-o-sparkles')
                                ->color('warning')
                                ->tooltip('Generate ulang SKU barang menggunakan prefix ini sekarang')
                                ->visible(fn (?Branch $record): bool => $record !== null && BranchResource::canEdit($record))
                                ->requiresConfirmation()
                                ->modalHeading('Generate Ulang SKU Barang?')
                                ->modalDescription('SKU produk akan disesuaikan memakai Prefix SKU di atas dan Format Kode SKU Baru (bawaan: hanya tambah/ganti prefix dan pertahankan kode ujung). Barcode, harga, stok, dan riwayat transaksi tidak akan berubah.')
                                ->action(function (?Branch $record, $state, $get): void {
                                    if (! $record || ! BranchResource::canEdit($record)) {
                                        return;
                                    }

                                    try {
                                        $result = app(RegenerateBranchProductSkus::class)->execute(
                                            branch: $record,
                                            prefixOverride: (string) ($state ?? $record->sku_prefix),
                                            scope: (string) ($get('sku_regenerate_scope') ?: RegenerateBranchProductSkus::SCOPE_ALL),
                                            mode: (string) ($get('sku_regenerate_mode') ?: RegenerateBranchProductSkus::MODE_MISSING_PREFIX),
                                            format: (string) ($get('sku_regenerate_format') ?: RegenerateBranchProductSkus::FORMAT_KEEP_SUFFIX),
                                            savePrefixToBranch: true,
                                        );

                                        Notification::make()
                                            ->title('Generate SKU Berhasil')
                                            ->body(RegenerateBranchProductSkus::formatResultMessage($result))
                                            ->success()
                                            ->send();
                                    } catch (\InvalidArgumentException $e) {
                                        Notification::make()
                                            ->title('Prefix SKU Belum Diisi')
                                            ->body($e->getMessage())
                                            ->danger()
                                            ->send();
                                    }
                                })
                        ),

                    TextInput::make('sku_last_number')
                        ->label('Nomor Urut SKU Terakhir')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->helperText('Produk baru berikutnya akan memakai nomor urut setelah angka ini (otomatis diperbarui saat simpan produk, Generate SKU, atau Pulihkan SKU Master).')
                        ->afterStateHydrated(function (TextInput $component, ?Branch $record): void {
                            if (! $record || blank($record->sku_prefix)) {
                                $component->state(0);

                                return;
                            }

                            $prefix = RegenerateBranchProductSkus::normalizePrefix($record->sku_prefix);
                            $lastNumber = \App\Models\SkuSequence::query()
                                ->where('prefix', $prefix)
                                ->value('last_number');

                            $component->state(
                                $lastNumber !== null
                                    ? (int) $lastNumber
                                    : \App\Helpers\ProductHelper::findMaxNumericSuffix($prefix)
                            );
                        }),

                    Toggle::make('regenerate_skus_on_save')
                        ->label('Generate ulang SKU barang yang sudah diimport saat simpan cabang')
                        ->helperText('Aktifkan jika ingin men-generate ulang SKU barang di katalog secara otomatis saat cabang ini disimpan.')
                        ->default(false)
                        ->live(),

                    Select::make('sku_regenerate_scope')
                        ->label('Cakupan Produk Target')
                        ->options(fn (?Branch $record): array => RegenerateBranchProductSkus::scopeOptions($record))
                        ->default(RegenerateBranchProductSkus::SCOPE_ALL)
                        ->native(false)
                        ->visible(fn ($get): bool => (bool) $get('regenerate_skus_on_save')),

                    Select::make('sku_regenerate_mode')
                        ->label('Mode Penggantian SKU')
                        ->options(RegenerateBranchProductSkus::modeOptions())
                        ->default(RegenerateBranchProductSkus::MODE_MISSING_PREFIX)
                        ->native(false)
                        ->visible(fn ($get): bool => (bool) $get('regenerate_skus_on_save')),

                    Select::make('sku_regenerate_format')
                        ->label('Format Kode SKU Baru')
                        ->options(RegenerateBranchProductSkus::formatOptions())
                        ->default(RegenerateBranchProductSkus::FORMAT_KEEP_SUFFIX)
                        ->native(false)
                        ->visible(fn ($get): bool => (bool) $get('regenerate_skus_on_save')),

                    TextInput::make('journal_prefix')
                        ->label('Prefix Kode Jurnal')
                        ->placeholder('Contoh: JV-PTK, JV-SKW')
                        ->helperText('Prefix untuk format nomor jurnal umum transaksi akuntansi cabang ini (misal: JV-PTK -> JV-PTK-20261005-0001)')
                        ->maxLength(20),
                ]),
        ];
    }
}
