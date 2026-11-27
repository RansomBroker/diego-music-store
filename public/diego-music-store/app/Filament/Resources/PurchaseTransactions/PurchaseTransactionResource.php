<?php

namespace App\Filament\Resources\PurchaseTransactions;

use App\Filament\Resources\PurchaseTransactions\Pages\CreatePurchaseTransaction;
use App\Filament\Resources\PurchaseTransactions\Pages\EditPurchaseTransaction;
use App\Filament\Resources\PurchaseTransactions\Pages\ListPurchaseTransactions;
use App\Filament\Resources\PurchaseTransactions\Schemas\PurchaseTransactionForm;
use App\Filament\Resources\PurchaseTransactions\Tables\PurchaseTransactionsTable;
use App\Models\PurchaseTransaction;
use BackedEnum;
use App\Filament\Concerns\HasResourcePermissions;
use App\Helpers\PermissionHelper;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PurchaseTransactionResource extends Resource
{
    use HasResourcePermissions;

    protected static string $permissionPrefix = 'bo.purchase_transactions';

    protected static ?string $model = PurchaseTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static ?string $recordTitleAttribute = 'transaction_no';

    public static function getNavigationGroup(): ?string
    {
        return 'Pembelian';
    }

    public static function getLabel(): ?string
    {
        return 'Transaksi Pembelian';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Transaksi Pembelian';
    }

    public static function form(Schema $schema): Schema
    {
        return PurchaseTransactionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PurchaseTransactionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPurchaseTransactions::route('/'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('branch_id', \App\Helpers\BranchHelper::getActiveBranchId());
    }

    public static function canEditPosted(): bool
    {
        return PermissionHelper::canAccessBackofficeFeature(auth()->user(), 'bo.purchase_transactions.edit_posted');
    }

    public static function canDeletePosted(): bool
    {
        return PermissionHelper::canAccessBackofficeFeature(auth()->user(), 'bo.purchase_transactions.delete_posted');
    }

    public static function canEdit(Model $record): bool
    {
        if ($record->status === 'posted') {
            return static::canEditPosted();
        }

        if ($record->status === 'draft') {
            return PermissionHelper::canAccessBackofficeFeature(auth()->user(), 'bo.purchase_transactions.edit');
        }

        return false;
    }

    public static function canDelete(Model $record): bool
    {
        if ($record->status === 'posted') {
            return static::canDeletePosted();
        }

        if ($record->status === 'draft') {
            return PermissionHelper::canAccessBackofficeFeature(auth()->user(), 'bo.purchase_transactions.delete');
        }

        return false;
    }

    public static function canDeleteAny(): bool
    {
        return PermissionHelper::canAccessBackofficeFeature(auth()->user(), 'bo.purchase_transactions.delete')
            || static::canDeletePosted();
    }
}
