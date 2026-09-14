<?php

namespace App\Filament\Pages\Settings;

use App\Actions\Settings\UpdatePoSmartAssistSettings;
use App\Models\PoSmartAssistSetting;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PoSmartAssistSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \UnitEnum|string|null $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Smart Assist PO';

    protected static ?string $title = 'Pengaturan Smart Assist PO';

    protected static string|\BackedEnum|null $navigationIcon = \Filament\Support\Icons\Heroicon::OutlinedSparkles;

    protected string $view = 'filament.pages.settings.po-smart-assist-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $setting = PoSmartAssistSetting::getSettings();

        $this->form->fill([
            'is_enabled' => $setting->is_enabled,
            'min_buffer_percentage' => $setting->min_buffer_percentage,
            'min_buffer_nominal' => $setting->min_buffer_nominal,
            'include_pending_pos' => $setting->include_pending_pos,
            'include_sales_projection' => $setting->include_sales_projection,
            'warning_threshold_days' => $setting->warning_threshold_days,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Konfigurasi Analisis Keuangan Smart Assist')
                    ->description('Atur ambang batas dan preferensi perhitungan risiko keuangan untuk transaksi Purchase Order (PO).')
                    ->schema([
                        Toggle::make('is_enabled')
                            ->label('Aktifkan Smart Assist PO')
                            ->helperText('Jika diaktifkan, sistem akan memberikan rekomendasi real-time saat pembuatan PO.')
                            ->default(true),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('min_buffer_percentage')
                                    ->label('Minimal Sisa Kas Buffer (%)')
                                    ->helperText('Batas persentase sisa kas pasca-PO agar berstatus AMAN.')
                                    ->numeric()
                                    ->suffix('%')
                                    ->required()
                                    ->default(20),

                                TextInput::make('min_buffer_nominal')
                                    ->label('Minimal Sisa Kas Nominal (Rp)')
                                    ->helperText('Nominal kas likuid fisik yang wajib disisakan toko.')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required()
                                    ->default(5000000),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Toggle::make('include_pending_pos')
                                    ->label('Sertakan PO Pending (Draft/Approved)')
                                    ->helperText('Hitung PO yang belum jadi Faktur Pembelian sebagai beban kas mendatang.')
                                    ->default(true),

                                Toggle::make('include_sales_projection')
                                    ->label('Sertakan Proyeksi Penjualan 30 Hari')
                                    ->helperText('Perhitungkan rata-rata arus masuk kas penjualan 30 hari terakhir.')
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        $formData = $this->form->getState();

        $action = new UpdatePoSmartAssistSettings();
        $action->execute($formData);

        Notification::make()
            ->title('Pengaturan Berhasil Disimpan')
            ->body('Konfigurasi Smart Assist PO telah diperbarui.')
            ->success()
            ->send();
    }
}
