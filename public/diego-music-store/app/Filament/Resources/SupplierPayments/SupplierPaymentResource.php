<?php

namespace App\Filament\Resources\SupplierPayments;

use App\Filament\Resources\SupplierPayments\Pages\CreateSupplierPayment;
use App\Filament\Resources\SupplierPayments\Pages\EditSupplierPayment;
use App\Filament\Resources\SupplierPayments\Pages\ListSupplierPayments;
use App\Filament\Resources\SupplierPayments\Schemas\SupplierPaymentForm;
use App\Filament\Resources\SupplierPayments\Tables\SupplierPaymentsTable;
use App\Models\SupplierPayment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SupplierPaymentResource extends Resource
{
    protected static ?string $model = SupplierPayment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $recordTitleAttribute = 'payment_no';

    public static function getNavigationGroup(): ?string
    {
        return 'Pembelian';
    }

    public static function getLabel(): ?string
    {
        return 'Pelunasan Hutang';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Pelunasan Hutang';
    }

    public static function form(Schema $schema): Schema
    {
        return SupplierPaymentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SupplierPaymentsTable::configure($table);
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
            'index' => ListSupplierPayments::route('/'),
            'create' => CreateSupplierPayment::route('/create'),
            'edit' => EditSupplierPayment::route('/{record}/edit'),
        ];
    }
}
