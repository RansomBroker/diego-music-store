<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Branch;
use App\Models\PricingTier;
use App\Models\Customer;
use App\Models\Supplier;
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
        if (Branch::count() === 0) {
            Branch::create([
                'name' => 'Cabang Pusat (Back Office)',
                'address' => 'Jl. Bypass Ngurah Rai No. 123, Denpasar, Bali',
                'phone' => '081234567890',
                'is_active' => true,
            ]);
        }

        // Seed default pricing tier
        if (PricingTier::count() === 0) {
            PricingTier::create([
                'name' => 'Umum / Retail',
                'description' => 'Harga jual eceran standar untuk umum',
            ]);
        }

        // Seed default products
        $this->call(ProductSeeder::class);

        // Seed Chart of Accounts
        $this->call(AccountSeeder::class);

        // Seed Customers (Pontianak / Kalbar addresses)
        $customers = [
            [
                'name' => 'Budi Setiawan',
                'phone' => '081254321098',
                'email' => 'budi.setiawan@gmail.com',
                'address' => 'Jl. Gajah Mada No. 45, Pontianak, Kalimantan Barat',
                'is_loyalty_member' => true,
                'loyalty_points' => 120,
            ],
            [
                'name' => 'Siti Rahmawati',
                'phone' => '089678123456',
                'email' => 'siti.rahma@yahoo.com',
                'address' => 'Jl. Ahmad Yani, Komplek Perdana Square Blok D9, Pontianak, Kalimantan Barat',
                'is_loyalty_member' => true,
                'loyalty_points' => 50,
            ],
            [
                'name' => 'Hendry Wijaya',
                'phone' => '085299887766',
                'email' => 'hendry.wijaya@gmail.com',
                'address' => 'Jl. Tanjung Pura No. 112, Pontianak, Kalimantan Barat',
                'is_loyalty_member' => false,
                'loyalty_points' => 0,
            ],
        ];

        foreach ($customers as $customer) {
            Customer::updateOrCreate(
                ['phone' => $customer['phone']],
                $customer
            );
        }

        // Seed Suppliers (Pontianak / Kalbar addresses)
        $suppliers = [
            [
                'name' => 'Borneo Music Supplier',
                'contact_person' => 'Ahmad',
                'phone' => '0811567890',
                'email' => 'info@borneomusic.com',
                'address' => 'Jl. Imam Bonjol No. 88, Pontianak, Kalimantan Barat',
                'bank_name' => 'Bank Kalbar',
                'bank_account_number' => '1012345678',
                'bank_account_name' => 'PT Borneo Music Supplier',
                'outstanding_debt' => 5000000.00,
            ],
            [
                'name' => 'Symphony Khatulistiwa',
                'contact_person' => 'Dewi',
                'phone' => '081345678901',
                'email' => 'symphony.khatulistiwa@gmail.com',
                'address' => 'Jl. Teuku Umar No. 12, Pontianak, Kalimantan Barat',
                'bank_name' => 'BCA',
                'bank_account_number' => '0291234567',
                'bank_account_name' => 'Dewi Lestari',
                'outstanding_debt' => 0.00,
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::updateOrCreate(
                ['name' => $supplier['name']],
                $supplier
            );
        }
    }
}
