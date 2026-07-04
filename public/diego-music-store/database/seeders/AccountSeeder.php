<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed root header accounts
        $headers = [
            '1-0000' => ['name' => 'ASET', 'classification' => 'asset'],
            '2-0000' => ['name' => 'LIABILITAS', 'classification' => 'liability'],
            '3-0000' => ['name' => 'EKUITAS', 'classification' => 'equity'],
            '4-0000' => ['name' => 'PENDAPATAN', 'classification' => 'revenue'],
            '5-0000' => ['name' => 'HARGA POKOK PENJUALAN', 'classification' => 'expense'],
            '6-0000' => ['name' => 'BEBAN OPERASIONAL', 'classification' => 'expense'],
        ];

        $headerModels = [];
        foreach ($headers as $code => $data) {
            $headerModels[$code] = Account::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $data['name'],
                    'classification' => $data['classification'],
                    'is_header' => true,
                    'is_active' => true,
                ]
            );
        }

        // 2. Seed detail accounts mapped to headers
        $details = [
            [
                'code' => '1-1000',
                'name' => 'Kas Utama',
                'classification' => 'asset',
                'parent_code' => '1-0000',
            ],
            [
                'code' => '1-1010',
                'name' => 'Kas Kecil',
                'classification' => 'asset',
                'parent_code' => '1-0000',
            ],
            [
                'code' => '1-1100',
                'name' => 'Bank Utama',
                'classification' => 'asset',
                'parent_code' => '1-0000',
            ],
            [
                'code' => '1-1110',
                'name' => 'Bank BCA',
                'classification' => 'asset',
                'parent_code' => '1-0000',
            ],
            [
                'code' => '1-1200',
                'name' => 'Piutang Dagang',
                'classification' => 'asset',
                'parent_code' => '1-0000',
            ],
            [
                'code' => '1-1300',
                'name' => 'Persediaan Barang Dagang',
                'classification' => 'asset',
                'parent_code' => '1-0000',
            ],
            [
                'code' => '2-1000',
                'name' => 'Hutang Dagang',
                'classification' => 'liability',
                'parent_code' => '2-0000',
            ],
            [
                'code' => '3-1000',
                'name' => 'Modal Pemilik',
                'classification' => 'equity',
                'parent_code' => '3-0000',
            ],
            [
                'code' => '4-1000',
                'name' => 'Pendapatan Penjualan',
                'classification' => 'revenue',
                'parent_code' => '4-0000',
            ],
            [
                'code' => '5-1000',
                'name' => 'Harga Pokok Penjualan (HPP)',
                'classification' => 'expense',
                'parent_code' => '5-0000',
            ],
            [
                'code' => '6-1000',
                'name' => 'Beban Operasional & Gaji',
                'classification' => 'expense',
                'parent_code' => '6-0000',
            ],
        ];

        foreach ($details as $acc) {
            Account::updateOrCreate(
                ['code' => $acc['code']],
                [
                    'name' => $acc['name'],
                    'classification' => $acc['classification'],
                    'is_header' => false,
                    'parent_id' => $headerModels[$acc['parent_code']]->id,
                    'is_active' => true,
                ]
            );
        }
    }
}
