<?php

namespace App\Filament\Resources\ScheduledJournalEntries;

use App\Filament\Resources\ScheduledJournalEntries\Pages\ListScheduledJournalEntries;
use App\Filament\Resources\ScheduledJournalEntries\Schemas\ScheduledJournalEntryForm;
use App\Filament\Resources\ScheduledJournalEntries\Tables\ScheduledJournalEntriesTable;
use App\Models\ScheduledJournalEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ScheduledJournalEntryResource extends Resource
{
    protected static ?string $model = ScheduledJournalEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static ?string $recordTitleAttribute = 'description';

    protected static ?string $modelLabel = 'Jurnal Umum Terjadwal';

    protected static ?string $pluralModelLabel = 'Jurnal Umum Terjadwal';

    public static function getNavigationGroup(): ?string
    {
        return 'Akuntansi';
    }

    public static function form(Schema $schema): Schema
    {
        return ScheduledJournalEntryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ScheduledJournalEntriesTable::configure($table);
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
            'index' => ListScheduledJournalEntries::route('/'),
        ];
    }
}
