<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchMonthlyOffQuotaSeeder extends Seeder
{
    /**
     * Set the default monthly day-off quota without overwriting
     * branch-specific quotas that an owner has already configured.
     */
    public function run(): void
    {
        Branch::query()
            ->whereNull('monthly_off_days_quota')
            ->update(['monthly_off_days_quota' => 4]);
    }
}
