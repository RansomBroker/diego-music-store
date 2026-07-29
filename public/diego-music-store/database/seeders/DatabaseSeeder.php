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
        $this->call(UserSeeder::class);
        $this->call(UnitSeeder::class);

        // Seed default branch (Back Office / Cabang Pusat)
        if (Branch::count() === 0) {
            Branch::create([
                'name' => 'Cabang Pusat (Back Office)',
                'address' => 'Jl. Gajah Mada No. 21-22, Pontianak, Kalimantan Barat',
                'phone' => '0561-734567',
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

        // Seed Chart of Accounts
        $this->call(AccountSeeder::class);

        // Seed default payment methods (9 Parent Categories + Sub-methods)
        $cashAcc     = \App\Models\Account::where('code', '1-1000')->first();
        $bankBcaAcc  = \App\Models\Account::where('code', '1-1110')->first();
        $bankUtamaAcc= \App\Models\Account::where('code', '1-1100')->first();
        $piutangAcc  = \App\Models\Account::where('code', '1-1200')->first();
        $voucherAcc  = \App\Models\Account::where('code', '4-2000')->first();
        $entertainAcc= \App\Models\Account::where('code', '6-2000')->first();

        $methodsData = [
            [
                'name' => 'Cash',
                'code' => 'cash',
                'account_id' => $cashAcc?->id,
                'parent_id' => null,
                'children' => [],
            ],
            [
                'name' => 'Debit Card',
                'code' => 'debit_card',
                'account_id' => $bankBcaAcc?->id,
                'parent_id' => null,
                'children' => [
                    ['name' => 'BCA', 'code' => 'debit-bca', 'account_id' => $bankBcaAcc?->id],
                    ['name' => 'BNI', 'code' => 'debit-bni', 'account_id' => null],
                    ['name' => 'Mandiri', 'code' => 'debit-mandiri', 'account_id' => null],
                    ['name' => 'BRI', 'code' => 'debit-bri', 'account_id' => null],
                ],
            ],
            [
                'name' => 'Credit Card',
                'code' => 'credit_card',
                'account_id' => $bankBcaAcc?->id,
                'parent_id' => null,
                'children' => [
                    ['name' => 'BCA', 'code' => 'credit-bca', 'account_id' => $bankBcaAcc?->id],
                    ['name' => 'Mandiri', 'code' => 'credit-mandiri', 'account_id' => null],
                    ['name' => 'Visa / Mastercard', 'code' => 'credit-visa-master', 'account_id' => null],
                ],
            ],
            [
                'name' => 'Entertain',
                'code' => 'entertain',
                'account_id' => $entertainAcc?->id,
                'parent_id' => null,
                'children' => [],
            ],
            [
                'name' => 'Piutang',
                'code' => 'credit',
                'account_id' => $piutangAcc?->id,
                'parent_id' => null,
                'children' => [],
            ],
            [
                'name' => 'Voucher',
                'code' => 'voucher',
                'account_id' => $voucherAcc?->id,
                'parent_id' => null,
                'children' => [],
            ],
            [
                'name' => 'QRIS',
                'code' => 'qris',
                'account_id' => $bankUtamaAcc?->id,
                'parent_id' => null,
                'children' => [],
            ],
            [
                'name' => 'Transfer',
                'code' => 'transfer',
                'account_id' => $bankUtamaAcc?->id,
                'parent_id' => null,
                'children' => [
                    ['name' => 'Transfer BCA', 'code' => 'transfer-bca', 'account_id' => $bankBcaAcc?->id],
                    ['name' => 'Transfer Mandiri', 'code' => 'transfer-mandiri', 'account_id' => null],
                ],
            ],
            [
                'name' => 'Other Payment',
                'code' => 'other_payment',
                'account_id' => $cashAcc?->id,
                'parent_id' => null,
                'children' => [],
            ],
        ];

        foreach ($methodsData as $group) {
            $parent = \App\Models\PaymentMethod::updateOrCreate(
                ['code' => $group['code']],
                [
                    'name' => $group['name'],
                    'account_id' => $group['account_id'],
                    'parent_id' => null,
                    'is_active' => true,
                ]
            );

            foreach ($group['children'] as $child) {
                \App\Models\PaymentMethod::updateOrCreate(
                    ['code' => $child['code']],
                    [
                        'name' => $child['name'],
                        'account_id' => $child['account_id'],
                        'parent_id' => $parent->id,
                        'is_active' => true,
                    ]
                );
            }
        }

        // Seed default products
        $this->call(ProductSeeder::class);

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

        $this->call(PurchaseOrderSeeder::class);
        $this->call(PurchaseTransactionSeeder::class);
        $this->call(DeliveryOrderSeeder::class);

        // Seed Sample Vouchers
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
            \App\Models\Voucher::updateOrCreate(
                ['code' => $vch['code']],
                $vch
            );
        }
    }
}
