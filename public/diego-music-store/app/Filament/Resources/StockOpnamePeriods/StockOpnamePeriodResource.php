<?php

namespace App\Filament\Resources\StockOpnamePeriods;

use App\Filament\Resources\StockOpnamePeriods\Pages\CreateStockOpnamePeriod;
use App\Filament\Resources\StockOpnamePeriods\Pages\EditStockOpnamePeriod;
use App\Filament\Resources\StockOpnamePeriods\Pages\ListStockOpnamePeriods;
use App\Filament\Resources\StockOpnamePeriods\Schemas\StockOpnamePeriodForm;
use App\Filament\Resources\StockOpnamePeriods\Tables\StockOpnamePeriodsTable;
use App\Models\StockOpnamePeriod;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StockOpnamePeriodResource extends Resource
{
    protected static ?string $model = StockOpnamePeriod::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationLabel = 'Periode Stok Opname';
    protected static ?string $modelLabel = 'Periode Stok Opname';
    protected static ?string $pluralModelLabel = 'Periode Stok Opname';

    public static function getNavigationGroup(): ?string
    {
        return 'Inventori';
    }

    public static function form(Schema $schema): Schema
    {
        return StockOpnamePeriodForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockOpnamePeriodsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockOpnamePeriods::route('/'),
            'create' => CreateStockOpnamePeriod::route('/create'),
            'edit' => EditStockOpnamePeriod::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user && !$user->hasRole(['owner', 'admin', 'super_admin', 'Owner', 'Admin', 'Super Admin'])) {
            return $query->whereIn('branch_id', $user->branches()->pluck('branches.id'));
        }

        $activeBranchId = \App\Helpers\BranchHelper::getActiveBranchId();
        return $activeBranchId ? $query->where('branch_id', $activeBranchId) : $query;
    }
}
