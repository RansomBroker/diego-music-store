<?php

namespace App\Filament\Resources\AccountClassifications;

use App\Filament\Resources\AccountClassifications\Pages\ListAccountClassifications;
use App\Filament\Resources\AccountClassifications\Schemas\AccountClassificationForm;
use App\Filament\Resources\AccountClassifications\Tables\AccountClassificationsTable;
use App\Models\AccountClassification;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AccountClassificationResource extends Resource
{
    protected static ?string $model = AccountClassification::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Klasifikasi Akun';

    protected static ?string $pluralModelLabel = 'Klasifikasi Akun';

    public static function getNavigationGroup(): ?string
    {
        return 'Akuntansi';
    }

    public static function form(Schema $schema): Schema
    {
        return AccountClassificationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccountClassificationsTable::configure($table);
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
            'index' => ListAccountClassifications::route('/'),
        ];
    }
}
