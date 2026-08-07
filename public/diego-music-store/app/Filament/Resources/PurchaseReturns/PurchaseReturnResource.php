<?php

namespace App\Filament\Resources\PurchaseReturns;

use App\Filament\Resources\PurchaseReturns\Pages\ListPurchaseReturns;
use App\Filament\Resources\PurchaseReturns\Tables\PurchaseReturnsTable;
use App\Models\PurchaseReturn;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PurchaseReturnResource extends Resource
{
    protected static ?string $model = PurchaseReturn::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static ?string $recordTitleAttribute = 'return_no';

    protected static ?string $navigationLabel = 'Retur Pembelian (Supplier)';

    protected static ?string $modelLabel = 'Retur Pembelian';

    protected static ?string $pluralModelLabel = 'Retur Pembelian';

    public static function getNavigationGroup(): ?string
    {
        return 'Pembelian';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return PurchaseReturnsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPurchaseReturns::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user && !$user->hasRole(['owner', 'admin', 'super_admin', 'Owner', 'Admin', 'Super Admin'])) {
            $userBranchIds = $user->branches()->pluck('branches.id')->toArray();
            return $query->whereIn('branch_id', $userBranchIds);
        }

        $activeBranchId = \App\Helpers\BranchHelper::getActiveBranchId();
        if ($activeBranchId) {
            return $query->where('branch_id', $activeBranchId);
        }

        return $query;
    }
}
