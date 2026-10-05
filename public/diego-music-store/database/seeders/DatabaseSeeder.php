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
        $this->call(FocusProductRuleSeeder::class);

        // Seed default branches (Cabang Pusat & Cabang Singkawang)
        if (Branch::count() === 0) {
            Branch::create([
                'name' => 'Cabang Pusat (Back Office)',
                'store_name' => 'Diego Music Store Pusat',
                'sku_prefix' => 'DG',
                'journal_prefix' => 'JV-PST',
                'address' => 'Jl. Gajah Mada No. 21-22, Pontianak, Kalimantan Barat',
                'phone' => '0561-734567',
                'is_active' => true,
            ]);
            Branch::create([
                'name' => 'Cabang Singkawang',
                'store_name' => 'Diego Music Store Singkawang',
                'sku_prefix' => 'DG',
                'journal_prefix' => 'JV-SKW',
                'address' => 'Jl. Diponegoro No. 88, Singkawang, Kalimantan Barat',
                'phone' => '0562-631234',
                'is_active' => true,
            ]);
        } elseif (Branch::count() === 1) {
            Branch::firstOrCreate(
                ['name' => 'Cabang Singkawang'],
                [
                    'store_name' => 'Diego Music Store Singkawang',
                    'sku_prefix' => 'DG',
                    'journal_prefix' => 'JV-SKW',
                    'address' => 'Jl. Diponegoro No. 88, Singkawang, Kalimantan Barat',
                    'phone' => '0562-631234',
                    'is_active' => true,
                ]
            );
        }

        // Pastikan default sku_prefix terisi 'DG' jika kosong atau 'PST'
        Branch::where('sku_prefix', 'PST')->orWhereNull('sku_prefix')->update(['sku_prefix' => 'DG']);

        // Seed default pricing tier
        if (PricingTier::count() === 0) {
            PricingTier::create([
                'name' => 'Umum / Retail',
                'description' => 'Harga jual eceran standar untuk umum',
            ]);
        }

        // Seed Chart of Accounts
        $this->call(AccountSeeder::class);
        $this->call(BranchCoaSeeder::class);

        // Seed default payment methods (9 Parent Categories + Sub-methods)
        $cashAcc     = \App\Helpers\AccountHelper::findByCode('111101001');
        $bankBcaAcc  = \App\Helpers\AccountHelper::findByCode('111201001');
        $bankMandiri = \App\Helpers\AccountHelper::findByCode('111201002');
        $bankBri     = \App\Helpers\AccountHelper::findByCode('111201003');
        $bankBni     = \App\Helpers\AccountHelper::findByCode('111201004');
        $bankDanamon = \App\Helpers\AccountHelper::findByCode('111201005');
        
        $tokopediaAcc= \App\Helpers\AccountHelper::findByCode('111202006');
        $shopeeAcc   = \App\Helpers\AccountHelper::findByCode('111202007');
        $piutangAcc  = \App\Helpers\AccountHelper::findByCode('111301001');
        $voucherAcc  = \App\Helpers\AccountHelper::findByCode('411301001') ?? \App\Helpers\AccountHelper::findByCode('611101027');
        $complimentaryAcc= \App\Helpers\AccountHelper::findByCode('611101026');

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
                    ['name' => 'BNI', 'code' => 'debit-bni', 'account_id' => $bankBni?->id],
                    ['name' => 'Mandiri', 'code' => 'debit-mandiri', 'account_id' => $bankMandiri?->id],
                    ['name' => 'BRI', 'code' => 'debit-bri', 'account_id' => $bankBri?->id],
                    ['name' => 'Danamon', 'code' => 'debit-danamon', 'account_id' => $bankDanamon?->id],
                ],
            ],
            [
                'name' => 'Credit Card',
                'code' => 'credit_card',
                'account_id' => $bankBcaAcc?->id,
                'parent_id' => null,
                'children' => [
                    ['name' => 'BCA', 'code' => 'credit-bca', 'account_id' => $bankBcaAcc?->id],
                    ['name' => 'Mandiri', 'code' => 'credit-mandiri', 'account_id' => $bankMandiri?->id],
                    ['name' => 'Visa / Mastercard', 'code' => 'credit-visa-master', 'account_id' => $bankBcaAcc?->id],
                ],
            ],
            [
                'name' => 'Complimentary',
                'code' => 'complimentary',
                'account_id' => $complimentaryAcc?->id,
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
                'account_id' => $bankBcaAcc?->id,
                'parent_id' => null,
                'children' => [],
            ],
            [
                'name' => 'Transfer',
                'code' => 'transfer',
                'account_id' => $bankBcaAcc?->id,
                'parent_id' => null,
                'children' => [
                    ['name' => 'Transfer BCA', 'code' => 'transfer-bca', 'account_id' => $bankBcaAcc?->id],
                    ['name' => 'Transfer Mandiri', 'code' => 'transfer-mandiri', 'account_id' => $bankMandiri?->id],
                    ['name' => 'Transfer BNI', 'code' => 'transfer-bni', 'account_id' => $bankBni?->id],
                    ['name' => 'Transfer BRI', 'code' => 'transfer-bri', 'account_id' => $bankBri?->id],
                    ['name' => 'Transfer Danamon', 'code' => 'transfer-danamon', 'account_id' => $bankDanamon?->id],
                ],
            ],
            [
                'name' => 'Marketplace',
                'code' => 'marketplace',
                'account_id' => $tokopediaAcc?->id ?? $shopeeAcc?->id,
                'parent_id' => null,
                'children' => [
                    ['name' => 'Tokopedia', 'code' => 'marketplace-tokopedia', 'account_id' => $tokopediaAcc?->id],
                    ['name' => 'Shopee', 'code' => 'marketplace-shopee', 'account_id' => $shopeeAcc?->id],
                ],
            ],
        ];

        // Hapus metode pembayaran lama 'entertain' jika masih ada
        \App\Models\PaymentMethod::where('code', 'entertain')->delete();

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


        $this->call(CommissionSchemeSeeder::class);
        $this->call(AttendanceViolationRuleSeeder::class);
        $this->call(KpiTemplateSeeder::class);
    }
}
