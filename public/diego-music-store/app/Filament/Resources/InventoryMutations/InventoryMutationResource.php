<?php

namespace App\Filament\Resources\InventoryMutations;

use App\Filament\Resources\InventoryMutations\Pages\CreateInventoryMutation;
use App\Filament\Resources\InventoryMutations\Pages\EditInventoryMutation;
use App\Filament\Resources\InventoryMutations\Pages\ListInventoryMutations;
use App\Filament\Resources\InventoryMutations\Schemas\InventoryMutationForm;
use App\Filament\Resources\InventoryMutations\Tables\InventoryMutationsTable;
use App\Models\InventoryMutation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InventoryMutationResource extends Resource
{
    protected static ?string $model = InventoryMutation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static ?string $recordTitleAttribute = 'mutation_number';

    protected static ?string $navigationLabel = 'Mutasi Barang';

    protected static ?string $modelLabel = 'Mutasi Barang';

    protected static ?string $pluralModelLabel = 'Mutasi Barang';

    public static function getNavigationGroup(): ?string
    {
        return 'Inventori';
    }

    public static function form(Schema $schema): Schema
    {
        return InventoryMutationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InventoryMutationsTable::configure($table);
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
            'index' => ListInventoryMutations::route('/'),
            'create' => CreateInventoryMutation::route('/create'),
            'edit' => EditInventoryMutation::route('/{record}/edit'),
        ];
    }
}
