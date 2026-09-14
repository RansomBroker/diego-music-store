<?php

namespace App\Filament\Resources\AttendanceViolationRules;

use App\Filament\Resources\AttendanceViolationRules\Pages\ListAttendanceViolationRules;
use App\Models\AttendanceViolationRule;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class AttendanceViolationRuleResource extends Resource
{
    protected static ?string $model = AttendanceViolationRule::class;

    protected static ?string $navigationLabel = 'Aturan Denda Presensi';

    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): ?string
    {
        return 'Manajemen Karyawan';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Aturan')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('violation_type')
                    ->label('Jenis Pelanggaran')
                    ->options([
                        'late_in' => 'Keterlambatan (Late In)',
                        'early_out' => 'Pulang Cepat (Early Out)',
                        'unexcused_absence' => 'Mangkir / Tanpa Keterangan',
                        'leave_over_quota' => 'Izin Melampaui Kuota',
                        'custom' => 'Kustom',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('min_minutes')
                    ->label('Min. Menit')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Forms\Components\TextInput::make('max_minutes')
                    ->label('Max. Menit (Opsional)')
                    ->numeric()
                    ->nullable(),
                Forms\Components\Select::make('deduction_type')
                    ->label('Tipe Denda')
                    ->options([
                        'fixed_amount' => 'Nominal Flat (Rp)',
                        'percentage_per_minute' => 'Nominal Per Menit (Rp/menit)',
                        'percentage_daily_salary' => 'Persentase Gaji Harian (%)',
                        'per_occurrence' => 'Per Kejadian',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('deduction_amount')
                    ->label('Nominal Denda (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Aturan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('violation_type')
                    ->label('Jenis Pelanggaran')
                    ->badge(),
                Tables\Columns\TextColumn::make('min_minutes')
                    ->label('Min. Menit'),
                Tables\Columns\TextColumn::make('max_minutes')
                    ->label('Max. Menit')
                    ->placeholder('Tanpa Batas'),
                Tables\Columns\TextColumn::make('deduction_amount')
                    ->label('Nominal Denda')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAttendanceViolationRules::route('/'),
        ];
    }
}
