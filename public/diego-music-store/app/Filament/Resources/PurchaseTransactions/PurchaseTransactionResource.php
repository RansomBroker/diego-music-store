<?php

namespace App\Filament\Resources\PurchaseTransactions;

use App\Filament\Resources\PurchaseTransactions\Pages\CreatePurchaseTransaction;
use App\Filament\Resources\PurchaseTransactions\Pages\EditPurchaseTransaction;
use App\Filament\Resources\PurchaseTransactions\Pages\ListPurchaseTransactions;
use App\Filament\Resources\PurchaseTransactions\Schemas\PurchaseTransactionForm;
use App\Filament\Resources\PurchaseTransactions\Tables\PurchaseTransactionsTable;
use App\Models\PurchaseTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PurchaseTransactionResource extends Resource
{
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
            'create' => CreatePurchaseTransaction::route('/create'),
            'edit' => EditPurchaseTransaction::route('/{record}/edit'),
        ];
    }
}
