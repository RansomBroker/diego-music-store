<?php

namespace App\Console\Commands;

use App\Actions\Branch\EnsureBranchCoaAccounts;
use Illuminate\Console\Command;

class EnsureBranchCoaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'branch:ensure-coa';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure all branches have inventory, interbranch receivable, and payable COA accounts configured';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai sinkronisasi akun COA cabang...');
        $count = EnsureBranchCoaAccounts::executeAll();
        $this->info("Berhasil memeriksa & menyinkronkan akun COA untuk {$count} cabang.");

        return Command::SUCCESS;
    }
}
