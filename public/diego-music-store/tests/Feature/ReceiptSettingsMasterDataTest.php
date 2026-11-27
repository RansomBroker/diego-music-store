<?php

namespace Tests\Feature;

use App\Actions\Setting\UpdateReceiptSettings;
use App\Filament\Resources\ReceiptSettings\ReceiptSettingResource;
use App\Helpers\ReceiptFormatterHelper;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ReceiptSetting;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceiptSettingsMasterDataTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);

        $this->branch = Branch::create([
            'name' => 'Cabang Pontianak',
            'code' => 'CBG-RCP-01',
            'store_name' => 'Diego Music Default',
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        $this->owner = User::factory()->create(['is_active' => true]);
        $this->owner->assignRole('owner');
        $this->owner->branches()->attach($this->branch->id);
    }

    public function test_receipt_setting_resource_rbac_and_thermal_text_formatting(): void
    {
        $this->actingAs($this->owner);

        $this->assertTrue(ReceiptSettingResource::canViewAny());
        $this->assertTrue(ReceiptSettingResource::canCreate());

        $setting = app(UpdateReceiptSettings::class)->execute($this->branch->id, [
            'store_display_name' => 'DIEGO MUSIC CUSTOM',
            'header_text' => 'Promo Akhir Tahun!',
            'footer_text' => 'Terima kasih sudah berbelanja di Diego!',
            'paper_width' => '58mm',
            'show_logo' => true,
            'show_customer' => false,
            'show_cashier' => true,
            'show_tax_details' => true,
            'invoice_footer_notes' => 'Transfer ke BCA 9876543210 a.n Diego Music',
        ]);

        $product = Product::create([
            'name' => 'Gitar Yamaha F310',
            'category' => 'Gitar',
            'type' => 'physical',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Natural',
            'sku' => 'DMS-P-0001',
            'price' => 1750000,
            'is_active' => true,
        ]);

        $sale = Sale::create([
            'invoice_number' => 'INV-TEST-0001',
            'branch_id' => $this->branch->id,
            'sales_rep_id' => $this->owner->id,
            'invoice_date' => now()->format('Y-m-d'),
            'subtotal' => 1750000,
            'discount_amount' => 0,
            'tax_amount' => 192500,
            'grand_total' => 1942500,
            'paid_amount' => 2000000,
            'change_amount' => 57500,
            'payment_method' => 'Tunai',
            'status' => 'completed',
            'created_by' => $this->owner->id,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => 1750000,
            'discount_amount' => 0,
            'total_price' => 1750000,
        ]);

        $sale->load(['branch', 'customer', 'salesRep', 'creator', 'items.variant.product', 'items.salesRep']);

        $rawText = ReceiptFormatterHelper::formatThermalText($sale, $setting);

        // Pastikan mengikuti Master Data Struk & Tagihan
        $this->assertStringContainsString('DIEGO MUSIC CUSTOM', $rawText);
        $this->assertStringContainsString('Promo Akhir Tahun!', $rawText);
        $this->assertStringContainsString('Terima kasih sudah berbelanja di', $rawText);
        $this->assertStringContainsString('Kembalian', $rawText);
        $this->assertStringContainsString('57.500', $rawText);
        $this->assertStringContainsString('Sales', $rawText);
        $this->assertStringNotContainsString('Pelanggan', $rawText); // show_customer = false
        $this->assertStringNotContainsString('CETAK', $rawText); // tidak ada tombol HTML ikut tercetak
        $this->assertStringNotContainsString('TUTUP', $rawText);

        // Pastikan tiap baris tidak melebihi 32 karakter untuk 58mm
        foreach (explode("\n", $rawText) as $line) {
            $this->assertLessThanOrEqual(32, mb_strlen($line), "Baris melebihi 32 char: '{$line}'");
        }

        // Cek endpoint raw-text & format invoice membaca Master Data Struk & Tagihan
        $rawRes = $this->get(route('pos.receipt.raw-text', $sale));
        $rawRes->assertStatus(200);
        $rawRes->assertSee('DIEGO MUSIC CUSTOM');
        $rawRes->assertDontSee('CETAK');

        $invoiceRes = $this->get(route('pos.receipt', ['sale' => $sale, 'format' => 'invoice']));
        $invoiceRes->assertStatus(200);
        $invoiceRes->assertSee('DIEGO MUSIC CUSTOM');
        $invoiceRes->assertSee('Promo Akhir Tahun!');
        $invoiceRes->assertSee('Transfer ke BCA 9876543210 a.n Diego Music');
    }

    public function test_fo_receipt_settings_page_shows_both_hardware_and_design_tabs(): void
    {
        $this->actingAs($this->owner);

        $response = $this->get(route('pos.receipt-settings'));
        $response->assertStatus(200);
        $response->assertSee('Hardware Printer & Direct Agent (Test Page)', false);
        $response->assertSee('Desain & Format Struk / Tagihan', false);
    }
}
