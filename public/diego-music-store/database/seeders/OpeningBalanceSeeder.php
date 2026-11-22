<?php

namespace Database\Seeders;

use App\Actions\Accounting\SeedOpeningBalances;
use Illuminate\Database\Seeder;

class OpeningBalanceSeeder extends Seeder
{
    /**
     * Delegate opening balance seeding to the SeedOpeningBalances Action.
     */
    public function run(): void
    {
        app(SeedOpeningBalances::class)->execute();
    }
}
