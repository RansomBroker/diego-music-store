<?php

namespace App\Filament\Resources\DeliveryOrders;

use App\Filament\Resources\DeliveryOrders\Pages\CreateDeliveryOrder;
use App\Filament\Resources\DeliveryOrders\Pages\EditDeliveryOrder;
use App\Filament\Resources\DeliveryOrders\Pages\ListDeliveryOrders;
use App\Filament\Resources\DeliveryOrders\Schemas\DeliveryOrderForm;
use App\Filament\Resources\DeliveryOrders\Tables\DeliveryOrdersTable;
use App\Models\DeliveryOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DeliveryOrderResource extends Resource
{
    protected static ?string $model = DeliveryOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $recordTitleAttribute = 'do_number';

    protected static ?string $navigationLabel = 'Surat Jalan (DO)';

    protected static ?string $modelLabel = 'Surat Jalan (DO)';

    protected static ?string $pluralModelLabel = 'Surat Jalan (DO)';

    public static function getNavigationGroup(): ?string
    {
        return 'Penjualan';
    }

    public static function form(Schema $schema): Schema
    {
        return DeliveryOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeliveryOrdersTable::configure($table);
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
            'index' => ListDeliveryOrders::route('/'),
            'create' => CreateDeliveryOrder::route('/create'),
            'edit' => EditDeliveryOrder::route('/{record}/edit'),
        ];
    }
}
