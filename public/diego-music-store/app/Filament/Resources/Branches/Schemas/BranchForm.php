<?php

namespace App\Filament\Resources\Branches\Schemas;

use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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

                    Toggle::make('is_active')
                        ->label('Status Aktif Operasional')
                        ->default(true),
                ]),

            Step::make('Struk POS & Alokasi Staf')
                ->description('Pengaturan cetak nota struk POS dan alokasi pengguna/staf')
                ->schema([
                    Textarea::make('receipt_header')
                        ->rows(2)
                        ->maxLength(500)
                        ->label('Header Struk POS (Catatan Atas)')
                        ->placeholder('Contoh: Pusat Penjualan Alat Musik & Sound System Terlengkap'),

                    Textarea::make('receipt_footer')
                        ->rows(2)
                        ->maxLength(500)
                        ->label('Footer Struk POS (Pesan Terima Kasih)')
                        ->placeholder('Contoh: Barang yang sudah dibeli tidak dapat ditukar. Terima Kasih!'),

                    Select::make('users')
                        ->relationship('users', 'name')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->label('Alokasi Staf & Kasir Cabang')
                        ->helperText('Pilih staf/kasir yang diberikan akses ke cabang ini')
                        ->columnSpanFull(),
                ]),
        ];
    }
}
