<?php

namespace Tests\Feature;

use App\Filament\Pages\Reports\IncomeStatement;
use App\Filament\Pages\Reports\PurchaseReport;
use App\Filament\Pages\Reports\StockListReport;
use App\Livewire\PosReportsSales;
use App\Livewire\PosReportsStockPrices;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FieldLevelSensitivityTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;
    protected User $owner;
    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->branch = Branch::create([
            'name' => 'Cabang Pusat',
            'store_name' => 'Diego Store Main',
            'address' => 'Jl. Musik No. 1',
            'phone' => '08123456789',
            'is_active' => true,
        ]);

        // Owner user
        $this->owner = User::factory()->create([
            'name' => 'Owner Diego',
            'username' => 'diego_owner',
            'email' => 'owner@diego.test',
            'is_active' => true,
        ]);
        $this->owner->assignRole('owner');
        $this->owner->branches()->attach($this->branch->id);

        // Staff / Karyawan user
        $this->staff = User::factory()->create([
            'name' => 'Staff Kasir',
            'username' => 'staff_kasir',
            'email' => 'kasir@diego.test',
            'is_active' => true,
        ]);
        $this->staff->assignRole('karyawan');
        $this->staff->branches()->attach($this->branch->id);
    }

    public function test_staff_cannot_see_hpp_and_asset_valuation_in_pos_stock_report(): void
    {
        $product = Product::create([
            'name' => 'Gitar Akustik Yamaha FS800',
            'type' => 'physical',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Natural Gloss',
            'sku' => 'GTR-FS800-NAT',
            'price' => 2500000,
            'cost_price' => 1750000,
            'hpp' => 1750000,
            'is_active' => true,
        ]);

        ProductBranchStock::create([
            'product_variant_id' => $variant->id,
            'branch_id' => $this->branch->id,
            'stock' => 5,
        ]);

        Livewire::actingAs($this->staff)
            ->test(PosReportsStockPrices::class)
            ->assertStatus(200)
            ->assertSee('Gitar Akustik Yamaha FS800')
            ->assertSee('2.500.000') // Selling price is visible
            ->assertDontSee('Total Nilai Aset (HPP)')
            ->assertDontSee('Harga Beli / HPP');
    }

    public function test_owner_can_see_hpp_and_asset_valuation_in_pos_stock_report(): void
    {
        $product = Product::create([
            'name' => 'Keyboard Roland RD-88',
            'type' => 'physical',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Black',
            'sku' => 'KBD-RD88',
            'price' => 18000000,
            'cost_price' => 14000000,
            'hpp' => 14000000,
            'is_active' => true,
        ]);

        ProductBranchStock::create([
            'product_variant_id' => $variant->id,
            'branch_id' => $this->branch->id,
            'stock' => 2,
        ]);

        Livewire::actingAs($this->owner)
            ->test(PosReportsStockPrices::class)
            ->assertStatus(200)
            ->assertSee('Keyboard Roland RD-88')
            ->assertSee('Total Nilai Aset (HPP)')
            ->assertSee('Harga Beli (HPP)')
            ->assertSee('14.000.000');
    }

    public function test_staff_cannot_see_cogs_and_gross_profit_in_pos_sales_report(): void
    {
        $sale = Sale::create([
            'branch_id' => $this->branch->id,
            'sales_rep_id' => $this->staff->id,
            'invoice_number' => 'INV-TEST-PROFIT-001',
            'invoice_date' => now()->toDateString(),
            'subtotal' => 1000000,
            'grand_total' => 1000000,
            'payment_method' => 'cash',
            'status' => 'completed',
            'created_by' => $this->staff->id,
        ]);

        Livewire::actingAs($this->staff)
            ->test(PosReportsSales::class)
            ->assertStatus(200)
            ->assertSee('Laporan Penjualan ERP')
            ->assertDontSee('Total HPP Barang')
            ->assertDontSee('Laba Kotor (Gross Profit)');
    }

    public function test_owner_can_see_cogs_and_gross_profit_in_pos_sales_report(): void
    {
        $sale = Sale::create([
            'branch_id' => $this->branch->id,
            'sales_rep_id' => $this->owner->id,
            'invoice_number' => 'INV-TEST-PROFIT-002',
            'invoice_date' => now()->toDateString(),
            'subtotal' => 2000000,
            'grand_total' => 2000000,
            'payment_method' => 'cash',
            'status' => 'completed',
            'created_by' => $this->owner->id,
        ]);

        Livewire::actingAs($this->owner)
            ->test(PosReportsSales::class)
            ->assertStatus(200)
            ->assertSee('Total HPP Barang')
            ->assertSee('Laba Kotor (Gross Profit)');
    }

    public function test_user_with_explicit_permission_can_view_sensitive_fields(): void
    {
        // Grant explicit inventory.view_cost to staff
        $this->staff->givePermissionTo('inventory.view_cost');

        $product = Product::create([
            'name' => 'Amplifier Marshall Code 50',
            'type' => 'physical',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Standard',
            'sku' => 'AMP-MARSH-50',
            'price' => 4500000,
            'cost_price' => 3200000,
            'hpp' => 3200000,
            'is_active' => true,
        ]);

        ProductBranchStock::create([
            'product_variant_id' => $variant->id,
            'branch_id' => $this->branch->id,
            'stock' => 1,
        ]);

        Livewire::actingAs($this->staff)
            ->test(PosReportsStockPrices::class)
            ->assertStatus(200)
            ->assertSee('Total Nilai Aset (HPP)')
            ->assertSee('Harga Beli (HPP)');
    }

    public function test_backoffice_reports_can_access_gates_unauthorized_users(): void
    {
        $this->actingAs($this->staff);

        $this->assertFalse(StockListReport::canAccess());
        $this->assertFalse(IncomeStatement::canAccess());
        $this->assertFalse(PurchaseReport::canAccess());

        $this->actingAs($this->owner);

        $this->assertTrue(StockListReport::canAccess());
        $this->assertTrue(IncomeStatement::canAccess());
        $this->assertTrue(PurchaseReport::canAccess());
    }
}
