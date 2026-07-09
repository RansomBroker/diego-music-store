<?php

namespace App\Console\Commands;

use App\Actions\Accounting\ProcessScheduledJournalEntries;
use Illuminate\Console\Command;

class ProcessScheduledJournals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-scheduled-journals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process and generate all pending scheduled general journal entries';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing scheduled journal entries...');
        
        $count = app(ProcessScheduledJournalEntries::class)->execute();
        
        $this->info("Successfully processed. {$count} journal entries created.");
        
        return Command::SUCCESS;
    }
}
