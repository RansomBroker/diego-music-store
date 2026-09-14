<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\CommissionScheme;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleCategory;
use App\Models\SalesCommissionLog;
use Illuminate\Database\Seeder;

class CommissionSchemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branch = Branch::first();
        $product = Product::first();
        $category = SaleCategory::first();

        // 1. Skema Komisi Sales General (Persentase 2.5%)
        $schemeGeneral = CommissionScheme::updateOrCreate(
            ['name' => 'Komisi Sales General (2.5%)'],
            [
                'branch_id' => $branch?->id,
                'calculation_type' => 'percentage',
                'rate' => 2.5,
                'applies_to' => 'all_sales',
                'target_product_id' => null,
                'target_sale_category_id' => null,
                'min_monthly_sales_target' => 5000000,
                'is_active' => true,
            ]
        );

        // 2. Skema Bonus Flat Produk Premium (Rp 50.000 / Transaksi)
        $schemeFlat = CommissionScheme::updateOrCreate(
            ['name' => 'Bonus Unit High-End (Flat Rp 50.000)'],
            [
                'branch_id' => $branch?->id,
                'calculation_type' => 'fixed_amount',
                'rate' => 50000,
                'applies_to' => $product ? 'product' : 'all_sales',
                'target_product_id' => $product?->id,
                'target_sale_category_id' => null,
                'min_monthly_sales_target' => 0,
                'is_active' => true,
            ]
        );

        // 3. Skema Komisi Kategori Aksesoris (Persentase 5.0%)
        $schemeCategory = CommissionScheme::updateOrCreate(
            ['name' => 'Komisi Spesifik Kategori Aksesoris (5.0%)'],
            [
                'branch_id' => $branch?->id,
                'calculation_type' => 'percentage',
                'rate' => 5.0,
                'applies_to' => $category ? 'category' : 'all_sales',
                'target_product_id' => null,
                'target_sale_category_id' => $category?->id,
                'min_monthly_sales_target' => 0,
                'is_active' => true,
            ]
        );

        // 4. Skema Insentif Omset Bertingkat (> Rp 25.000.000 - 3.5%)
        $schemeTiered = CommissionScheme::updateOrCreate(
            ['name' => 'Insentif Omset Bertingkat Sales (> Rp 25jt - 3.5%)'],
            [
                'branch_id' => $branch?->id,
                'calculation_type' => 'percentage',
                'rate' => 3.5,
                'applies_to' => 'all_sales',
                'target_product_id' => null,
                'target_sale_category_id' => null,
                'min_monthly_sales_target' => 25000000,
                'is_active' => true,
            ]
        );

        // Sample Commission Logs untuk demo rekapitulasi
        $employees = Employee::where('is_active', true)->take(3)->get();
        $today = now()->format('Y-m-d');

        foreach ($employees as $idx => $emp) {
            $sampleSaleAmount = 1500000 * ($idx + 1);
            $commissionAmount = $sampleSaleAmount * 0.025;

            SalesCommissionLog::updateOrCreate(
                [
                    'employee_id' => $emp->id,
                    'date' => $today,
                ],
                [
                    'commission_scheme_id' => $schemeGeneral->id,
                    'sale_amount' => $sampleSaleAmount,
                    'commission_amount' => $commissionAmount,
                    'status' => $idx === 0 ? 'approved' : 'pending',
                    'notes' => "Komisi contoh penjualan transaksi demo #{$emp->id}",
                ]
            );
        }
    }
}
