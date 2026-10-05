<?php

namespace Database\Seeders;

use App\Actions\Branch\EnsureBranchCoaAccounts;
use Illuminate\Database\Seeder;

class BranchCoaSeeder extends Seeder
{
    public function run(): void
    {
        $count = EnsureBranchCoaAccounts::executeAll();
        $this->command->info("Berhasil sinkronisasi akun COA untuk {$count} cabang.");
    }
}
