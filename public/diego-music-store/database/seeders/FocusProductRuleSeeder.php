<?php

namespace Database\Seeders;

use App\Models\FocusProductRule;
use Illuminate\Database\Seeder;

class FocusProductRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rules = [
            [
                'name' => 'Dead Stock',
                'code' => 'dead_stock',
                'description' => 'Stok produk > 0 namun tidak ada transaksi penjualan sama sekali dalam 6 bulan terakhir.',
                'conditions' => [
                    'period_months' => 6,
                    'max_sales_qty' => 0,
                ],
                'priority' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Slow Moving',
                'code' => 'slow_moving',
                'description' => 'Stok produk > 0 dan pergerakan penjualan lambat (terjual kurang dari 3 unit dalam 6 bulan terakhir).',
                'conditions' => [
                    'period_months' => 6,
                    'max_sales_qty' => 2,
                ],
                'priority' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Aging Stock',
                'code' => 'aging_stock',
                'description' => 'Stok produk yang mengendap di cabang sudah berusia lebih dari 180 hari berdasarkan mutasi stok masuk.',
                'conditions' => [
                    'min_aging_days' => 180,
                ],
                'priority' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Overstock',
                'code' => 'overstock',
                'description' => 'Stok produk jauh di atas kebutuhan berdasarkan velocity / kecepatan penjualan.',
                'conditions' => [
                    'months_of_inventory_threshold' => 12,
                ],
                'priority' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Stock Value at Risk',
                'code' => 'stock_value_at_risk',
                'description' => 'Nilai HPP modal stok tinggi (> Rp 5.000.000) dengan velocity perputaran penjualan rendah.',
                'conditions' => [
                    'min_stock_value' => 5000000,
                    'max_sales_qty' => 1,
                ],
                'priority' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($rules as $ruleData) {
            FocusProductRule::updateOrCreate(
                ['code' => $ruleData['code']],
                $ruleData
            );
        }
    }
}
