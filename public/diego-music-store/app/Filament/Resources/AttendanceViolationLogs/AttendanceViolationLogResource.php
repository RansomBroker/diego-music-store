<?php

namespace App\Filament\Resources\AttendanceViolationLogs;

use App\Filament\Resources\AttendanceViolationLogs\Pages\ListAttendanceViolationLogs;
use App\Models\AttendanceViolationLog;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AttendanceViolationLogResource extends Resource
{
    protected static ?string $model = AttendanceViolationLog::class;

    protected static ?string $navigationLabel = 'Log Denda Presensi';

    protected static ?int $navigationSort = 6;

    public static function getNavigationGroup(): ?string
    {
        return 'Manajemen Karyawan';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('employee_id')
                    ->label('Karyawan')
                    ->relationship('employee', 'name')
                    ->required(),
                Forms\Components\DatePicker::make('date')
                    ->label('Tanggal')
                    ->required(),
                Forms\Components\Select::make('violation_type')
                    ->label('Jenis Pelanggaran')
                    ->options([
                        'late_in' => 'Keterlambatan (Late In)',
                        'early_out' => 'Pulang Cepat (Early Out)',
                        'unexcused_absence' => 'Mangkir / Tanpa Keterangan',
                        'leave_over_quota' => 'Izin Melampaui Kuota',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('late_early_minutes')
                    ->label('Durasi Menit')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('deduction_amount')
                    ->label('Nominal Denda (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Status Approval')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Disetujui (Approved)',
                        'waived' => 'Waived (Dihapuskan)',
                        'deducted_in_payroll' => 'Sudah Masuk Slip Gaji',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('notes')
                    ->label('Catatan')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Karyawan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('violation_type')
                    ->label('Pelanggaran')
                    ->badge(),
                Tables\Columns\TextColumn::make('late_early_minutes')
                    ->label('Durasi')
                    ->formatStateUsing(fn ($state) => "{$state} Menit"),
                Tables\Columns\TextColumn::make('deduction_amount')
                    ->label('Nominal Denda')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'info' => 'waived',
                        'primary' => 'deducted_in_payroll',
                    ]),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => Auth::id(),
                        ]);
                    }),
                Action::make('waive')
                    ->label('Waive')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'waived',
                            'approved_by' => Auth::id(),
                        ]);
                    }),
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
            'index' => ListAttendanceViolationLogs::route('/'),
        ];
    }
}
