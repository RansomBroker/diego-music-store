<?php

namespace App\Filament\Resources\Payrolls\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PayrollItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components(static::getComponents());
    }

    public static function getComponents(): array
    {
        return [
            Section::make('Pendapatan & Tunjangan (Penambahan)')
                ->description('Komponen penambah gaji karyawan')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('basic_salary')
                                ->label('Gaji Pokok')
                                ->numeric()
                                ->prefix('Rp')
                                ->default(0)
                                ->required()
                                ->live(onBlur: true),

                            TextInput::make('allowance_amount')
                                ->label('Tunjangan Tetap / Tambahan')
                                ->numeric()
                                ->prefix('Rp')
                                ->default(0)
                                ->live(onBlur: true),

                            TextInput::make('overtime_amount')
                                ->label('Tunjangan Lembur')
                                ->numeric()
                                ->prefix('Rp')
                                ->default(0)
                                ->live(onBlur: true),

                            TextInput::make('commission_amount')
                                ->label('Komisi Penjualan Sales')
                                ->numeric()
                                ->prefix('Rp')
                                ->default(0)
                                ->live(onBlur: true),

                            TextInput::make('kpi_bonus_amount')
                                ->label('Bonus Pencapaian KPI')
                                ->numeric()
                                ->prefix('Rp')
                                ->default(0)
                                ->columnSpanFull()
                                ->live(onBlur: true),
                        ]),
                ]),

            Section::make('Potongan & Denda (Pengurangan)')
                ->description('Komponen pengurang gaji karyawan')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('violation_deduction_amount')
                                ->label('Denda Presensi & Keterlambatan')
                                ->numeric()
                                ->prefix('Rp')
                                ->default(0)
                                ->live(onBlur: true),

                            TextInput::make('other_deduction_amount')
                                ->label('Potongan Lainnya / Kasbon')
                                ->numeric()
                                ->prefix('Rp')
                                ->default(0)
                                ->live(onBlur: true),
                        ]),
                ]),

            Section::make('Catatan Penyesuaian')
                ->schema([
                    Textarea::make('notes')
                        ->label('Catatan Keterangan Penyesuaian')
                        ->placeholder('Contoh: Penyesuaian bonus lembur proyek khusus atau pelunasan kasbon...')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),

            Section::make('Ringkasan Gaji Bersih (Take Home Pay)')
                ->schema([
                    Placeholder::make('net_salary_summary')
                        ->label('')
                        ->content(function ($get) {
                            $basic = floatval($get('basic_salary') ?? 0);
                            $allowance = floatval($get('allowance_amount') ?? 0);
                            $overtime = floatval($get('overtime_amount') ?? 0);
                            $commission = floatval($get('commission_amount') ?? 0);
                            $kpi = floatval($get('kpi_bonus_amount') ?? 0);

                            $violation = floatval($get('violation_deduction_amount') ?? 0);
                            $otherDeduction = floatval($get('other_deduction_amount') ?? 0);

                            $totalEarnings = $basic + $allowance + $overtime + $commission + $kpi;
                            $totalDeductions = $violation + $otherDeduction;
                            $netSalary = max(0.0, $totalEarnings - $totalDeductions);

                            return view('filament.pages.payroll.components.edit-summary-banner', [
                                'totalEarnings'   => $totalEarnings,
                                'totalDeductions' => $totalDeductions,
                                'netSalary'       => $netSalary,
                            ]);
                        })
                        ->columnSpanFull(),
                ]),
        ];
    }
}
