<?php

namespace App\Filament\Resources\JournalEntries\Widgets;

use App\Models\JournalEntry;
use App\Models\JournalItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class JournalEntryStats extends BaseWidget
{
    protected function getStats(): array
    {
        $draftCount = JournalEntry::where('status', 'draft')->count();
        $postedCount = JournalEntry::where('status', 'posted')->count();
        
        // Sum total debits of all posted entries
        $totalPostedAmount = JournalItem::whereHas('journalEntry', function ($q) {
            $q->where('status', 'posted');
        })->sum('debit');

        return [
            Stat::make('Total Jurnal Posted', $postedCount)
                ->description('Jumlah transaksi jurnal yang sudah dibukukan')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
                
            Stat::make('Total Nilai Transaksi', \App\Helpers\FormatHelper::rupiah($totalPostedAmount))
                ->description('Akumulasi nominal debit yang sudah ter-posting')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),

            Stat::make('Jurnal Draft (Pending)', $draftCount)
                ->description('Transaksi yang masih memerlukan review / balance check')
                ->descriptionIcon('heroicon-m-document-text')
                ->color($draftCount > 0 ? 'amber' : 'gray'),
        ];
    }
}
