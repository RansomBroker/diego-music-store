<?php

namespace App\Filament\Resources\CustomerLabels;

use App\Filament\Resources\CustomerLabels\Pages\ListCustomerLabels;
use App\Filament\Resources\CustomerLabels\Schemas\CustomerLabelForm;
use App\Filament\Resources\CustomerLabels\Tables\CustomerLabelsTable;
use App\Models\CustomerLabel;
use BackedEnum;
use App\Filament\Concerns\HasResourcePermissions;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CustomerLabelResource extends Resource
{
    use HasResourcePermissions;

    protected static string $permissionPrefix = 'bo.customer_labels';

    protected static ?string $model = CustomerLabel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookmark;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Label Pelanggan';

    protected static ?string $pluralModelLabel = 'Label Pelanggan';

    public static function getNavigationGroup(): ?string
    {
        return 'Master Data';
    }

    public static function form(Schema $schema): Schema
    {
        return CustomerLabelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerLabelsTable::configure($table);
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
            'index' => ListCustomerLabels::route('/'),
        ];
    }
}
