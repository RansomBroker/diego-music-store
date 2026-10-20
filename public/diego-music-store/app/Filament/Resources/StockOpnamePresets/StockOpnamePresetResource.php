<?php

namespace App\Filament\Resources\StockOpnamePresets;

use App\Filament\Resources\StockOpnamePresets\Pages\ListStockOpnamePresets;
use App\Filament\Resources\StockOpnamePresets\Schemas\StockOpnamePresetForm;
use App\Filament\Resources\StockOpnamePresets\Tables\StockOpnamePresetsTable;
use App\Models\StockOpnamePreset;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StockOpnamePresetResource extends Resource
{
    protected static ?string $model = StockOpnamePreset::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationLabel = 'Preset Lokasi Opname';
    protected static ?string $modelLabel = 'Preset Lokasi Opname';
    protected static ?string $pluralModelLabel = 'Preset Lokasi Opname';

    public static function getNavigationGroup(): ?string
    {
        return 'Inventori';
    }

    public static function form(Schema $schema): Schema
    {
        return StockOpnamePresetForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockOpnamePresetsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockOpnamePresets::route('/'),
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
