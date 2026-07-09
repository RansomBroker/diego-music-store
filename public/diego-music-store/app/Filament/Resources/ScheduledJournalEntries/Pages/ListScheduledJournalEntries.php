<?php

namespace App\Filament\Resources\ScheduledJournalEntries\Pages;

use App\Actions\Accounting\CreateScheduledJournalEntry;
use App\Filament\Resources\ScheduledJournalEntries\ScheduledJournalEntryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;

class ListScheduledJournalEntries extends ListRecords
{
    protected static string $resource = ScheduledJournalEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat Jadwal Jurnal Baru')
                ->modalWidth('4xl')
                ->using(fn (array $data): Model => app(CreateScheduledJournalEntry::class)->execute($data)),
        ];
    }
}
