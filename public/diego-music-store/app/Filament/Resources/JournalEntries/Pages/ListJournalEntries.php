<?php

namespace App\Filament\Resources\JournalEntries\Pages;

use App\Filament\Resources\JournalEntries\JournalEntryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListJournalEntries extends ListRecords
{
    protected static string $resource = JournalEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('xl')
                ->using(fn (array $data): \Illuminate\Database\Eloquent\Model => app(\App\Actions\Accounting\CreateJournalEntry::class)->execute($data)),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\JournalEntries\Widgets\JournalEntryStats::class,
        ];
    }
}
