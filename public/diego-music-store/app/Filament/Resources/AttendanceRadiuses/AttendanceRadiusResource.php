<?php

namespace App\Filament\Resources\AttendanceRadiuses;

use App\Filament\Resources\AttendanceRadiuses\Pages\ListAttendanceRadiuses;
use App\Models\Branch;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttendanceRadiusResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?string $navigationLabel = 'Radius Presensi';

    protected static ?string $pluralModelLabel = 'Radius Presensi Cabang';

    protected static ?string $modelLabel = 'Radius Presensi Cabang';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'Manajemen Karyawan';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->disabled()
                    ->label('Nama Cabang'),

                TextInput::make('attendance_radius_meters')
                    ->numeric()
                    ->required()
                    ->default(100)
                    ->label('Radius Toleransi Absensi (Meter)')
                    ->helperText('Batas jarak maksimal karyawan dari lokasi cabang saat Clock In / Out'),

                TextInput::make('shift_start_time')
                    ->required()
                    ->default('09:00')
                    ->label('Jam Masuk Shift (HH:MM)')
                    ->placeholder('09:00')
                    ->helperText('Jam mulai kerja cabang. Karyawan yang Clock In setelah jam ini dianggap terlambat.'),

                TextInput::make('shift_end_time')
                    ->required()
                    ->default('17:00')
                    ->label('Jam Pulang Shift (HH:MM)')
                    ->placeholder('17:00')
                    ->helperText('Jam selesai kerja cabang. Karyawan yang Clock Out sebelum jam ini dianggap pulang cepat.'),

                TextInput::make('latitude')
                    ->numeric()
                    ->required()
                    ->readOnly()
                    ->label('Koordinat Latitude GPS')
                    ->placeholder('-0.03470087552402962'),

                TextInput::make('longitude')
                    ->numeric()
                    ->required()
                    ->readOnly()
                    ->label('Koordinat Longitude GPS')
                    ->placeholder('109.33239215349418'),

                ViewField::make('leaflet_map')
                    ->view('filament.components.leaflet-map-picker')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Cabang'),

                TextColumn::make('store_name')
                    ->searchable()
                    ->label('Nama Toko / Outlet'),

                TextColumn::make('address')
                    ->limit(40)
                    ->label('Alamat Cabang'),

                TextColumn::make('latitude')
                    ->numeric(decimalPlaces: 6)
                    ->label('Latitude'),

                TextColumn::make('longitude')
                    ->numeric(decimalPlaces: 6)
                    ->label('Longitude'),

                TextColumn::make('attendance_radius_meters')
                    ->badge()
                    ->color('info')
                    ->suffix(' Meter')
                    ->sortable()
                    ->label('Radius Toleransi'),

                TextColumn::make('shift_time')
                    ->label('Jam Shift Kerja')
                    ->badge()
                    ->color('info')
                    ->getStateUsing(fn ($record) => ($record->shift_start_time ? substr($record->shift_start_time, 0, 5) : '09:00') . ' - ' . ($record->shift_end_time ? substr($record->shift_end_time, 0, 5) : '17:00')),

                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Status Operasional'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make()
                    ->label('Edit Radius')
                    ->color('warning')
                    ->icon(Heroicon::OutlinedMapPin),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAttendanceRadiuses::route('/'),
        ];
    }
}
