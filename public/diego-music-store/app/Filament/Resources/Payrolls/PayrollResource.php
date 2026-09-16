<?php

namespace App\Filament\Resources\Payrolls;

use App\Filament\Resources\Payrolls\Pages\ListPayrolls;
use App\Models\PayrollItem;
use App\Filament\Resources\Payrolls\Schemas\PayrollItemForm;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class PayrollResource extends Resource
{
    protected static ?string $model = PayrollItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Payroll & Gaji Karyawan';

    protected static ?string $pluralModelLabel = 'Payroll & Gaji Karyawan';

    protected static ?string $modelLabel = 'Payroll Gaji Karyawan';

    protected static ?int $navigationSort = 9;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Manajemen Karyawan';
    }

    public static function form(Schema $schema): Schema
    {
        return PayrollItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.nik')
                    ->searchable()
                    ->sortable()
                    ->label('NIK'),

                TextColumn::make('employee.name')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Karyawan')
                    ->weight('bold'),

                TextColumn::make('payroll.period')
                    ->searchable()
                    ->sortable()
                    ->label('Periode'),

                TextColumn::make('branch.name')
                    ->searchable()
                    ->label('Cabang')
                    ->placeholder('Semua Cabang'),

                TextColumn::make('basic_salary')
                    ->numeric()
                    ->prefix('Rp ')
                    ->label('Gaji Pokok'),

                TextColumn::make('allowance_amount')
                    ->numeric()
                    ->prefix('Rp ')
                    ->label('Tunjangan'),

                TextColumn::make('overtime_amount')
                    ->numeric()
                    ->prefix('Rp ')
                    ->label('Tunj. Lembur')
                    ->color('info'),

                TextColumn::make('commission_amount')
                    ->numeric()
                    ->prefix('Rp ')
                    ->label('Komisi Sales'),

                TextColumn::make('kpi_bonus_amount')
                    ->numeric()
                    ->prefix('Rp ')
                    ->label('Bonus KPI'),

                TextColumn::make('violation_deduction_amount')
                    ->numeric()
                    ->prefix('Rp ')
                    ->color('danger')
                    ->label('Denda Presensi'),

                TextColumn::make('other_deduction_amount')
                    ->numeric()
                    ->prefix('Rp ')
                    ->color('warning')
                    ->label('Potongan Lain'),

                TextColumn::make('net_salary')
                    ->numeric()
                    ->prefix('Rp ')
                    ->weight('bold')
                    ->color('success')
                    ->label('Gaji Bersih (THP)'),

                TextColumn::make('payroll.status')
                    ->badge()
                    ->color(fn ($record) => match ($record->payroll?->status) {
                        'paid' => 'success',
                        'approved' => 'info',
                        'cancelled' => 'danger',
                        default => 'warning',
                    })
                    ->label('Status'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('branch_id')
                    ->relationship('branch', 'name')
                    ->label('Cabang'),

                Tables\Filters\SelectFilter::make('period')
                    ->relationship('payroll', 'period')
                    ->label('Periode Bulan'),

                Tables\Filters\SelectFilter::make('employee_id')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->label('Karyawan'),
            ])
            ->actions([
                Action::make('payslipPdf')
                    ->label('Slip PDF')
                    ->color('info')
                    ->icon('heroicon-o-printer')
                    ->url(fn ($record) => route('pos.payroll.payslip-pdf', $record->id))
                    ->openUrlInNewTab(),

                Action::make('exportExcel')
                    ->label('Slip Excel')
                    ->tooltip('Unduh Slip Gaji Excel Karyawan Ini')
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn ($record) => route('pos.payroll.item.export-excel', $record->id))
                    ->openUrlInNewTab(),

                EditAction::make()
                    ->label('Edit Gaji')
                    ->modalHeading(fn ($record) => 'Override Komponen Gaji: ' . ($record->employee?->name ?? 'Karyawan') . ($record->employee?->nik ? " ({$record->employee->nik})" : ''))
                    ->modalWidth('2xl')
                    ->modalSubmitActionLabel('Simpan & Override Gaji')
                    ->form(PayrollItemForm::getComponents())
                    ->after(function ($record) {
                        // Recalculate net salary per person
                        $earnings = $record->basic_salary + $record->allowance_amount + $record->overtime_amount + $record->commission_amount + $record->kpi_bonus_amount;
                        $deductions = $record->violation_deduction_amount + $record->other_deduction_amount;
                        $record->net_salary = max(0.0, $earnings - $deductions);
                        $record->save();

                        // Recalculate parent Payroll header totals
                        if ($record->payroll) {
                            $record->payroll->update([
                                'total_basic_salary' => (float) $record->payroll->items()->sum('basic_salary'),
                                'total_allowances'   => (float) $record->payroll->items()->sum(DB::raw('allowance_amount + overtime_amount')),
                                'total_commissions'  => (float) $record->payroll->items()->sum('commission_amount'),
                                'total_kpi_bonuses'  => (float) $record->payroll->items()->sum('kpi_bonus_amount'),
                                'total_deductions'   => (float) $record->payroll->items()->sum(DB::raw('violation_deduction_amount + other_deduction_amount')),
                                'total_net_salary'   => (float) $record->payroll->items()->sum('net_salary'),
                            ]);
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayrolls::route('/'),
        ];
    }
}
