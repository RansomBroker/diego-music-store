<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
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
