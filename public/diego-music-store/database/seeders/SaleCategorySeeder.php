<?php

namespace Database\Seeders;

use App\Models\SaleCategory;
use Illuminate\Database\Seeder;

class SaleCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Store' => 'STR',
            'Online' => 'ONL',
        ];

        foreach ($categories as $name => $prefix) {
            SaleCategory::updateOrCreate(
                ['name' => $name],
                [
                    'prefix' => $prefix,
                    'digit_length' => 4,
                ],
            );
        }
    }
}
