<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Budi Setiawan',
                'phone' => '081254321098',
                'email' => 'budi.setiawan@gmail.com',
                'address' => 'Jl. Gajah Mada No. 45, Pontianak, Kalimantan Barat',
                'is_loyalty_member' => true,
                'loyalty_points' => 120,
                'deposit_balance' => 150000.00,
            ],
            [
                'name' => 'Siti Rahmawati',
                'phone' => '089678123456',
                'email' => 'siti.rahma@yahoo.com',
                'address' => 'Jl. Ahmad Yani, Komplek Perdana Square Blok D9, Pontianak, Kalimantan Barat',
                'is_loyalty_member' => true,
                'loyalty_points' => 50,
                'deposit_balance' => 0.00,
            ],
            [
                'name' => 'Hendry Wijaya',
                'phone' => '085299887766',
                'email' => 'hendry.wijaya@gmail.com',
                'address' => 'Jl. Tanjung Pura No. 112, Pontianak, Kalimantan Barat',
                'is_loyalty_member' => false,
                'loyalty_points' => 0,
                'deposit_balance' => 500000.00,
            ],
        ];

        foreach ($customers as $customer) {
            Customer::updateOrCreate(
                ['phone' => $customer['phone']],
                $customer
            );
        }
    }
}
