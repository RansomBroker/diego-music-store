<?php

namespace App\Filament\Resources\SalesCommissions;

use App\Filament\Resources\SalesCommissions\Pages\ListSalesCommissions;
use App\Helpers\BranchHelper;
use App\Models\SalesCommissionLog;
use BackedEnum;
use App\Filament\Concerns\HasResourcePermissions;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SalesCommissionResource extends Resource
{
    use HasResourcePermissions;

    protected static string $permissionPrefix = 'bo.sales_commissions';
    protected static bool $hasCreatePermission = false;
    protected static bool $hasEditPermission = false;
    protected static bool $hasDeletePermission = false;

    protected static ?string $model = SalesCommissionLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Rekap & Log Komisi Sales';

    protected static ?string $pluralModelLabel = 'Log Komisi Sales';

    protected static ?string $modelLabel = 'Log Komisi Sales';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return 'Manajemen Karyawan';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Karyawan Sales')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sale_id')
                    ->label('ID Transaksi')
                    ->formatStateUsing(fn ($state) => '#' . $state),
                Tables\Columns\TextColumn::make('sale_amount')
                    ->label('Nilai Penjualan')
                    ->money('IDR', locale: 'id_ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('commission_amount')
                    ->label('Komisi Earned')
                    ->money('IDR', locale: 'id_ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'purple',
                        'paid' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(30),
            ])
            ->actions([
                \Filament\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (SalesCommissionLog $record) {
                        abort_unless(static::canViewAny(), 403);
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                        ]);
                    })
                    ->visible(fn (SalesCommissionLog $record) => $record->status === 'pending' && static::canViewAny()),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\BulkAction::make('approve_selected')
                        ->label('Approve Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn () => static::canViewAny())
                        ->action(function ($records) {
                            abort_unless(static::canViewAny(), 403);
                            $records->each->update([
                                'status' => 'approved',
                                'approved_by' => auth()->id(),
                            ]);
                        }),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $branchId = BranchHelper::getActiveBranchId();
        return parent::getEloquentQuery()
            ->whereHas('employee', fn (Builder $q) => $q->where('branch_id', $branchId));
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSalesCommissions::route('/'),
        ];
    }
}
