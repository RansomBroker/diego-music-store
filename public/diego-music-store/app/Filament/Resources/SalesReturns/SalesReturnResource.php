<?php

namespace App\Filament\Resources\SalesReturns;

use App\Filament\Resources\SalesReturns\Pages\ListSalesReturns;
use App\Filament\Resources\SalesReturns\Tables\SalesReturnsTable;
use App\Models\SalesReturn;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SalesReturnResource extends Resource
{
    protected static ?string $model = SalesReturn::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static ?string $recordTitleAttribute = 'return_number';

    protected static ?string $navigationLabel = 'Retur Penjualan (Barang)';

    protected static ?string $modelLabel = 'Retur Penjualan';

    protected static ?string $pluralModelLabel = 'Retur Penjualan';

    public static function getNavigationGroup(): ?string
    {
        return 'Inventori';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return SalesReturnsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSalesReturns::route('/'),
        ];
    }

    public static function getEloquentQuery(): \\Illuminate\\Database\\Eloquent\\Builder
    {
        return parent::getEloquentQuery()
            ->where('branch_id', \\App\\Helpers\\BranchHelper::getActiveBranchId());
    }
}
