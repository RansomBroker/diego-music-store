<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'code' => '1-1000',
                'name' => 'Kas Utama',
                'classification' => 'asset',
                'is_active' => true,
            ],
            [
                'code' => '1-1100',
                'name' => 'Bank Utama',
                'classification' => 'asset',
                'is_active' => true,
            ],
            [
                'code' => '1-1200',
                'name' => 'Piutang Dagang',
                'classification' => 'asset',
                'is_active' => true,
            ],
            [
                'code' => '1-1300',
                'name' => 'Persediaan Barang Dagang',
                'classification' => 'asset',
                'is_active' => true,
            ],
            [
                'code' => '2-1000',
                'name' => 'Hutang Dagang',
                'classification' => 'liability',
                'is_active' => true,
            ],
            [
                'code' => '3-1000',
                'name' => 'Modal Pemilik',
                'classification' => 'equity',
                'is_active' => true,
            ],
            [
                'code' => '4-1000',
                'name' => 'Pendapatan Penjualan',
                'classification' => 'revenue',
                'is_active' => true,
            ],
            [
                'code' => '5-1000',
                'name' => 'Harga Pokok Penjualan (HPP)',
                'classification' => 'expense',
                'is_active' => true,
            ],
            [
                'code' => '6-1000',
                'name' => 'Beban Operasional & Gaji',
                'classification' => 'expense',
                'is_active' => true,
            ],
        ];

        foreach ($accounts as $acc) {
            Account::updateOrCreate(
                ['code' => $acc['code']],
                [
                    'name' => $acc['name'],
                    'classification' => $acc['classification'],
                    'is_active' => $acc['is_active'],
                ]
            );
        }
    }
}
