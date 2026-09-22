<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Voucher;

class DemoSeeder extends Seeder
{
    /**
     * Seed dummy and sample data for testing and demo purposes.
     */
    public function run(): void
    {
        // 1. Ensure master system data exists first
        $this->call(DatabaseSeeder::class);

        // 2. Seed Customer & Supplier sample data
        $this->call(CustomerSeeder::class);
        $this->call(SupplierSeeder::class);

        // 3. Seed Product sample data & initial branch stock
        $this->call(ProductSeeder::class);

        // 4. Seed Purchase PO, Transactions & Delivery Orders
        $this->call(PurchaseOrderSeeder::class);
        $this->call(PurchaseTransactionSeeder::class);
        $this->call(DeliveryOrderSeeder::class);

        // 5. Seed Sample Vouchers
        $sampleVouchers = [
            [
                'code' => 'PROMO50K',
                'name' => 'Voucher Diskon Rp 50.000 Promo Toko',
                'type' => 'fixed',
                'value' => 50000,
                'min_spend' => 200000,
                'valid_until' => now()->addDays(30),
                'max_uses' => 50,
                'used_count' => 0,
                'is_active' => true,
            ],
            [
                'code' => 'DISKON10',
                'name' => 'Voucher Diskon 10% All Item',
                'type' => 'percent',
                'value' => 10,
                'min_spend' => 100000,
                'valid_until' => now()->addDays(60),
                'max_uses' => 100,
                'used_count' => 0,
                'is_active' => true,
            ],
            [
                'code' => 'HEBOH100K',
                'name' => 'Voucher Potongan Rp 100.000 Belanja Musik',
                'type' => 'fixed',
                'value' => 100000,
                'min_spend' => 500000,
                'valid_until' => now()->addDays(15),
                'max_uses' => 20,
                'used_count' => 0,
                'is_active' => true,
            ],
            [
                'code' => 'MEMBER15',
                'name' => 'Voucher Spesial Member Diskon 15%',
                'type' => 'percent',
                'value' => 15,
                'min_spend' => 300000,
                'valid_until' => now()->addDays(90),
                'max_uses' => 200,
                'used_count' => 0,
                'is_active' => true,
            ],
        ];

        foreach ($sampleVouchers as $vch) {
            Voucher::updateOrCreate(
                ['code' => $vch['code']],
                $vch
            );
        }
    }
}
