<?php

namespace App\Filament\Resources\JournalEntries\Pages;

use App\Actions\Accounting\CreateJournalEntry;
use App\Filament\Resources\JournalEntries\JournalEntryResource;
use App\Filament\Resources\JournalEntries\Widgets\JournalEntryStats;
use App\Filament\Resources\ScheduledJournalEntries\ScheduledJournalEntryResource;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;

class ListJournalEntries extends ListRecords
{
    protected static string $resource = JournalEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('scheduled_journals')
                ->label('Jurnal Terjadwal')
                ->icon('heroicon-o-calendar')
                ->color('gray')
                ->url(fn () => ScheduledJournalEntryResource::getUrl('index')),
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
