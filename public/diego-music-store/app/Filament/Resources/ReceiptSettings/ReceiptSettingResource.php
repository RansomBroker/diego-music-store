<?php

namespace App\Filament\Resources\ReceiptSettings;

use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\Resources\ReceiptSettings\Pages\ListReceiptSettings;
use App\Filament\Resources\ReceiptSettings\Schemas\ReceiptSettingForm;
use App\Filament\Resources\ReceiptSettings\Tables\ReceiptSettingsTable;
use App\Models\ReceiptSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReceiptSettingResource extends Resource
{
    use HasResourcePermissions;

    protected static string $permissionPrefix = 'bo.receipt_settings';

    protected static ?string $model = ReceiptSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $recordTitleAttribute = 'store_display_name';

    public static function getNavigationGroup(): ?string
    {
        return 'Master Data';
    }

    public static function getLabel(): ?string
    {
        return 'Struk & Tagihan';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Struk & Tagihan';
    }

    public static function form(Schema $schema): Schema
    {
        return ReceiptSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReceiptSettingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReceiptSettings::route('/'),
        ];
    }
}

