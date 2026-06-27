<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            [
                'name' => 'Pieces',
                'code' => 'pcs',
                'is_active' => true,
            ],
            [
                'name' => 'Set',
                'code' => 'set',
                'is_active' => true,
            ],
            [
                'name' => 'Unit',
                'code' => 'unit',
                'is_active' => true,
            ],
            [
                'name' => 'Pack',
                'code' => 'pack',
                'is_active' => true,
            ],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(
                ['code' => $unit['code']],
                $unit
            );
        }
    }
}
