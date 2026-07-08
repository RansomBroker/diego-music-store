<?php

namespace App\Filament\Resources\JournalEntries\Pages;

use App\Actions\Accounting\CreateJournalEntry;
use App\Filament\Resources\JournalEntries\JournalEntryResource;
use App\Filament\Resources\JournalEntries\Widgets\JournalEntryStats;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;

class ListJournalEntries extends ListRecords
{
    protected static string $resource = JournalEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('xl')
                ->using(fn (array $data): Model => app(CreateJournalEntry::class)->execute($data)),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            JournalEntryStats::class,
        ];
    }
}
