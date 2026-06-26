<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleAndPermissionSeeder::class);

        // Seed default branch (Back Office / Cabang Pusat)
        if (\App\Models\Branch::count() === 0) {
            \App\Models\Branch::create([
                'name' => 'Cabang Pusat (Back Office)',
                'address' => 'Jl. Bypass Ngurah Rai No. 123, Denpasar, Bali',
                'phone' => '081234567890',
                'is_active' => true,
            ]);
        }

        // Seed default pricing tier
        if (\App\Models\PricingTier::count() === 0) {
            \App\Models\PricingTier::create([
                'name' => 'Umum / Retail',
                'description' => 'Harga jual eceran standar untuk umum',
            ]);
        }

        // Seed default products
        $this->call(ProductSeeder::class);

        // Seed Chart of Accounts
        $this->call(AccountSeeder::class);
    }
}
