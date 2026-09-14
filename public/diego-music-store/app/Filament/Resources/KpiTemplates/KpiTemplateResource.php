<?php

namespace App\Filament\Resources\KpiTemplates;

use App\Filament\Resources\KpiTemplates\Pages\ListKpiTemplates;
use App\Models\KpiTemplate;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KpiTemplateResource extends Resource
{
    protected static ?string $model = KpiTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrophy;

    protected static ?string $navigationLabel = 'Template KPI Karyawan';

    protected static ?string $pluralModelLabel = 'Template KPI';

    protected static ?string $modelLabel = 'Template KPI';

    protected static ?int $navigationSort = 7;

    public static function getNavigationGroup(): ?string
    {
        return 'Manajemen Karyawan';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Nama Template KPI'),

                TextInput::make('position')
                    ->maxLength(255)
                    ->label('Jabatan (Position)')
                    ->placeholder('Contoh: Sales Executive / Cashier'),

                Select::make('employee_id')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->label('Karyawan Spesifik (Opsional Override)'),

                TextInput::make('max_bonus_amount')
                    ->numeric()
                    ->required()
                    ->default(500000)
                    ->label('Maksimum Bonus (Rp)'),

                TextInput::make('target_sales_amount')
                    ->numeric()
                    ->required()
                    ->default(10000000)
                    ->label('Target Omset Sales (Rp)'),

                TextInput::make('weight_sales')
                    ->numeric()
                    ->required()
                    ->default(40)
                    ->label('Bobot Sales (%)'),

                TextInput::make('target_atv_amount')
                    ->numeric()
                    ->required()
                    ->default(250000)
                    ->label('Target ATV (Rp)'),

                TextInput::make('weight_atv')
                    ->numeric()
                    ->required()
                    ->default(20)
                    ->label('Bobot ATV (%)'),

                TextInput::make('target_attendance_pct')
                    ->numeric()
                    ->required()
                    ->default(95)
                    ->label('Target Absensi (%)'),

                TextInput::make('weight_attendance')
                    ->numeric()
                    ->required()
                    ->default(20)
                    ->label('Bobot Absensi (%)'),

                TextInput::make('target_punctuality_pct')
                    ->numeric()
                    ->required()
                    ->default(95)
                    ->label('Target Ketepatan Waktu (%)'),

                TextInput::make('weight_punctuality')
                    ->numeric()
                    ->required()
                    ->default(20)
                    ->label('Bobot Ketepatan Waktu (%)'),

                Toggle::make('is_active')
                    ->default(true)
                    ->label('Status Aktif'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Template')
                    ->weight('bold'),

                TextColumn::make('position')
                    ->searchable()
                    ->label('Jabatan')
                    ->placeholder('Semua Jabatan')
                    ->badge()
                    ->color('info'),

                TextColumn::make('target_sales_amount')
                    ->numeric()
                    ->prefix('Rp ')
                    ->label('Target Sales'),

                TextColumn::make('target_atv_amount')
                    ->numeric()
                    ->prefix('Rp ')
                    ->label('Target ATV'),

                TextColumn::make('target_attendance_pct')
                    ->suffix('%')
                    ->label('Target Absensi'),

                TextColumn::make('max_bonus_amount')
                    ->numeric()
                    ->prefix('Rp ')
                    ->label('Max Bonus')
                    ->weight('bold')
                    ->color('warning'),

                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKpiTemplates::route('/'),
        ];
    }
}
