<?php

use App\Actions\Accounting\SeedOpeningBalances;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run once during deployment to ensure COA 122101001 exists and seed initial opening balances
     * strictly for accounts whose current ledger mutations are still zero.
     */
    public function up(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        app(SeedOpeningBalances::class)->execute();
    }

    /**
     * Intentionally non-destructive: never roll back posted opening or operational journals.
     */
    public function down(): void
    {
        // Non-destructive no-op
    }
};

