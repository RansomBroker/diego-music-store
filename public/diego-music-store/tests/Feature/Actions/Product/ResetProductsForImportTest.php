<?php

namespace Tests\Feature\Actions\Product;

use App\Actions\Product\ImportProducts;
use App\Actions\Product\ResetProductsForImport;
use App\Models\Branch;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ResetProductsForImportTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Cabang Pusat Reset Test',
            'address' => 'Jl. Gajah Mada',
            'phone' => '0561-123456',
            'is_active' => true,
        ]);

        Unit::firstOrCreate(['code' => 'pcs'], ['name' => 'Pieces', 'is_active' => true]);
    }

    public function test_resets_products_variants_stocks_and_initial_stock_journals(): void
    {
        $csvContent = <<<'CSV'
NO,KODE BARANG,NAMA STOK,KATEGORI BARANG,Harga Beli,Netto,Disc,HET,HARGA JUAL CASH,JLH.STOK
1,OLD-001,PRODUK LAMA,GUITAR,100000,100000,0,150000,150000,4 Pcs
CSV;
        $tempFile = tempnam(sys_get_temp_dir(), 'reset_test_') . '.csv';
        file_put_contents($tempFile, $csvContent);

        try {
            app(ImportProducts::class)->execute($tempFile, $this->branch->id);

            $this->assertEquals(1, Product::count());
            $this->assertEquals(1, ProductVariant::count());
            $this->assertEquals(1, ProductBranchStock::count());
            $this->assertEquals(1, StockMovement::count());
            $this->assertEquals(1, JournalEntry::where('reference_type', 'InitialStock')->count());

            $result = app(ResetProductsForImport::class)->execute();

            $this->assertEquals(1, $result['deleted_products']);
            $this->assertEquals(1, $result['deleted_variants']);
            $this->assertEquals(1, $result['deleted_journals']);
            $this->assertEquals(0, Product::count());
            $this->assertEquals(0, ProductVariant::count());
            $this->assertEquals(0, ProductBranchStock::count());
            $this->assertEquals(0, StockMovement::count());
            $this->assertEquals(0, JournalEntry::where('reference_type', 'InitialStock')->count());
        } finally {
            @unlink($tempFile);
        }
    }

    public function test_refuses_reset_when_products_have_operational_sales_history(): void
    {
        $user = User::factory()->create();

        $product = Product::create([
            'name' => 'Gitar Terjual',
            'type' => 'physical',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SOLD-001',
            'price' => 500000,
            'cost_price' => 300000,
            'hpp' => 300000,
            'is_active' => true,
        ]);

        $sale = Sale::create([
            'invoice_number' => 'INV-LOCK-001',
            'invoice_date' => now()->format('Y-m-d'),
            'branch_id' => $this->branch->id,
            'created_by' => $user->id,
            'sales_rep_id' => $user->id,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'subtotal' => 500000,
            'grand_total' => 500000,
            'status' => 'completed',
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => 500000,
            'total_price' => 500000,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Penjualan POS');

        try {
            app(ResetProductsForImport::class)->execute();
        } finally {
            $this->assertEquals(1, Product::count());
            $this->assertEquals(1, ProductVariant::count());
        }
    }

    public function test_enforces_delete_permission_when_authenticated(): void
    {
        Permission::findOrCreate('bo.products.delete', 'web');
        $staffRole = Role::firstOrCreate(['name' => 'staff_no_delete', 'guard_name' => 'web']);
        $staffRole->syncPermissions([]);

        $user = User::factory()->create();
        $user->assignRole($staffRole);
        $this->actingAs($user);

        $this->expectException(HttpException::class);
        app(ResetProductsForImport::class)->execute();
    }
}
