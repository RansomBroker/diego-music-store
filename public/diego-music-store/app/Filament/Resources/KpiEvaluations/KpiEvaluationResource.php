<?php

namespace App\Filament\Resources\KpiEvaluations;

use App\Actions\Kpi\ApproveKpiEvaluation;
use App\Filament\Resources\KpiEvaluations\Pages\ListKpiEvaluations;
use App\Models\KpiEvaluation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KpiEvaluationResource extends Resource
{
    protected static ?string $model = KpiEvaluation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Evaluasi & Bonus KPI';

    protected static ?string $pluralModelLabel = 'Evaluasi KPI';

    protected static ?string $modelLabel = 'Evaluasi KPI';

    protected static ?int $navigationSort = 8;

    public static function getNavigationGroup(): ?string
    {
        return 'Manajemen Karyawan';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->searchable()
                    ->sortable()
                    ->label('Karyawan')
                    ->weight('bold'),

                TextColumn::make('period')
                    ->searchable()
                    ->sortable()
                    ->label('Periode'),

                TextColumn::make('actual_sales_amount')
                    ->numeric()
                    ->prefix('Rp ')
                    ->label('Actual Sales'),

                TextColumn::make('actual_atv_amount')
                    ->numeric()
                    ->prefix('Rp ')
                    ->label('Actual ATV'),

                TextColumn::make('actual_attendance_pct')
                    ->suffix('%')
                    ->label('Absensi'),

                TextColumn::make('actual_punctuality_pct')
                    ->suffix('%')
                    ->label('Ketepatan Waktu'),

                TextColumn::make('final_kpi_score')
                    ->suffix('%')
                    ->badge()
                    ->color(fn ($record) => $record->final_kpi_score >= 90 ? 'success' : ($record->final_kpi_score >= 75 ? 'info' : 'danger'))
                    ->label('Skor Akhir KPI'),

                TextColumn::make('earned_bonus_amount')
                    ->numeric()
                    ->prefix('Rp ')
                    ->weight('bold')
                    ->color('warning')
                    ->label('Bonus Cair'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('branch_id')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Cabang'),

                Tables\Filters\SelectFilter::make('period')
                    ->options(fn () => KpiEvaluation::query()->distinct()->orderBy('period', 'desc')->pluck('period', 'period')->toArray())
                    ->label('Periode Bulan (YYYY-MM)'),
            ])
            ->actions([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKpiEvaluations::route('/'),
        ];
    }
}
