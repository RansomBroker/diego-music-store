<?php

namespace App\Filament\Resources\ReceiptSettings\Schemas;

use App\Models\Branch;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReceiptSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make([
                    'default' => 1,
                    'lg' => 5,
                ])
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(1)
                            ->columnSpan([
                                'default' => 1,
                                'lg' => 3,
                            ])
                            ->schema([
                                Section::make('Format Kertas & Identitas')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('branch_id')
                                                    ->label('Cabang')
                                                    ->options(fn () => Branch::orderBy('name')->pluck('name', 'id'))
                                                    ->required()
                                                    ->searchable()
                                                    ->preload()
                                                    ->unique(table: 'receipt_settings', column: 'branch_id', ignoreRecord: true)
                                                    ->disabled(fn ($record) => $record !== null)
                                                    ->dehydrated()
                                                    ->live()
                                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                                        if ($state && blank($get('store_display_name'))) {
                                                            $branch = Branch::find($state);
                                                            if ($branch) {
                                                                $set('store_display_name', $branch->store_name ?: $branch->name);
                                                            }
                                                        }
                                                    }),

                                                Select::make('paper_width')
                                                    ->label('Ukuran Kertas Printer')
                                                    ->options([
                                                        '80mm' => '80mm Standard Thermal (48 Karakter)',
                                                        '58mm' => '58mm Mini Thermal (32 Karakter)',
                                                        'A4' => 'A4 Full Page (Invoice / Faktur)',
                                                    ])
                                                    ->default('80mm')
                                                    ->selectablePlaceholder(false)
                                                    ->required()
                                                    ->live(),

                                                TextInput::make('store_display_name')
                                                    ->label('Judul Utama Struk & Tagihan')
                                                    ->placeholder('Contoh: Diego Music Store')
                                                    ->maxLength(255)
                                                    ->live(onBlur: true)
                                                    ->columnSpanFull(),

                                                TextInput::make('header_text')
                                                    ->label('Pesan Header / Sambutan Struk')
                                                    ->placeholder('Contoh: Selamat Datang! Layanan Musik Terbaik')
                                                    ->maxLength(500)
                                                    ->live(onBlur: true)
                                                    ->columnSpanFull(),
                                            ]),
                                    ]),

                                Section::make('Komponen Yang Ditampilkan')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Toggle::make('show_logo')
                                                    ->label('Tampilkan Logo Toko')
                                                    ->default(true)
                                                    ->live(),

                                                Toggle::make('show_customer')
                                                    ->label('Tampilkan Nama Pelanggan')
                                                    ->default(true)
                                                    ->live(),

                                                Toggle::make('show_cashier')
                                                    ->label('Tampilkan Nama Kasir')
                                                    ->default(true)
                                                    ->live(),

                                                Toggle::make('show_tax_details')
                                                    ->label('Tampilkan Detail PPN / Pajak')
                                                    ->default(true)
                                                    ->live(),
                                            ]),
                                    ]),

                                Section::make('Catatan Kaki Struk, Invoice & Tagihan')
                                    ->schema([
                                        Textarea::make('footer_text')
                                            ->label('Catatan Kaki Struk (Footer)')
                                            ->rows(2)
                                            ->maxLength(500)
                                            ->placeholder('Contoh: Terima Kasih atas Kunjungan Anda. Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.')
                                            ->live(onBlur: true),

                                        Textarea::make('invoice_footer_notes')
                                            ->label('Catatan Tambahan Invoice / Tagihan / Penawaran')
                                            ->rows(3)
                                            ->maxLength(1000)
                                            ->placeholder('Contoh: Pembayaran ditransfer ke BCA 123-456-7890 a.n Diego Music Store.')
                                            ->live(onBlur: true),
                                    ]),
                            ]),

                        Section::make('Live Preview Struk & Tagihan')
                            ->columnSpan([
                                'default' => 1,
                                'lg' => 2,
                            ])
                            ->schema([
                                Placeholder::make('receipt_preview')
                                    ->hiddenLabel()
                                    ->content(fn ($get) => view('backoffice.receipt-settings.live-preview', [
                                        'branchId' => $get('branch_id'),
                                        'store_display_name' => $get('store_display_name') ?? '',
                                        'header_text' => $get('header_text') ?? '',
                                        'footer_text' => $get('footer_text') ?? '',
                                        'invoice_footer_notes' => $get('invoice_footer_notes') ?? '',
                                        'paper_width' => $get('paper_width') ?: '80mm',
                                        'show_logo' => (bool) ($get('show_logo') ?? true),
                                        'show_customer' => (bool) ($get('show_customer') ?? true),
                                        'show_cashier' => (bool) ($get('show_cashier') ?? true),
                                        'show_tax_details' => (bool) ($get('show_tax_details') ?? true),
                                    ])),
                            ]),
                    ]),
            ]);
    }
}

