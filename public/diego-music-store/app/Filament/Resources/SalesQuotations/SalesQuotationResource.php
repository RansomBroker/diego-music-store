<?php

namespace App\Filament\Resources\SalesQuotations;

use App\Filament\Resources\SalesQuotations\Pages\ListSalesQuotations;
use App\Filament\Resources\SalesQuotations\Schemas\SalesQuotationForm;
use App\Filament\Resources\SalesQuotations\Tables\SalesQuotationsTable;
use App\Models\SalesQuotation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SalesQuotationResource extends Resource
{
    protected static ?string $model = SalesQuotation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static ?string $recordTitleAttribute = 'quotation_number';

    protected static ?string $navigationLabel = 'Penawaran Harga (SQ)';

    protected static ?string $modelLabel = 'Penawaran Harga';

    protected static ?string $pluralModelLabel = 'Penawaran Harga';

    public static function getNavigationGroup(): ?string
    {
        return 'Penjualan';
    }

    public static function form(Schema $schema): Schema
    {
        return SalesQuotationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SalesQuotationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSalesQuotations::route('/'),
        ];
    }
}
