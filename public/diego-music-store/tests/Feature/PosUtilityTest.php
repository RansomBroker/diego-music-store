<?php

namespace Tests\Feature;

use App\Actions\Privilege\CreateRole;
use App\Actions\Privilege\UpdateRolePermissions;
use App\Actions\Store\UpdateBranchProfile;
use App\Actions\Setting\UpdateReceiptSettings;
use App\Helpers\BarcodeHelper;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ReceiptSetting;
use App\Models\User;
use App\Livewire\PosPrivileges;
use App\Livewire\PosStoreProfile;
use App\Livewire\PosReceiptSettings;
use App\Livewire\PosBarcodePrint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PosUtilityTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Main Branch',
            'store_name' => 'Diego Store Test',
            'address' => 'Jakarta',
            'phone' => '08123456789',
            'is_active' => true,
        ]);

        $this->user = User::factory()->create([
            'name' => 'Test Admin',
            'username' => 'testadmin',
            'email' => 'admin@test.com',
            'is_active' => true,
        ]);

        $this->user->branches()->attach($this->branch->id);

        Permission::findOrCreate('pos.access', 'web');
        Permission::findOrCreate('pos.void', 'web');
        Permission::findOrCreate('pos.discount', 'web');
    }

    /** @test */
    public function it_can_create_role_and_update_permissions()
    {
        $createAction = new CreateRole();
        $role = $createAction->execute([
            'name' => 'Supervisor POS',
            'permissions' => ['pos.access', 'pos.void'],
        ]);

        $this->assertDatabaseHas('roles', ['name' => 'Supervisor POS']);
        $this->assertTrue($role->hasPermissionTo('pos.access'));

        $updateAction = new UpdateRolePermissions();
        $updateAction->execute($role, [
            'name' => 'Head Supervisor POS',
            'permissions' => ['pos.access', 'pos.discount'],
        ]);

        $this->assertDatabaseHas('roles', ['name' => 'Head Supervisor POS']);
        $this->assertTrue($role->hasPermissionTo('pos.discount'));
        $this->assertFalse($role->hasPermissionTo('pos.void'));
    }

    /** @test */
    public function it_can_update_branch_store_profile()
    {
        $action = new UpdateBranchProfile();
        $updated = $action->execute($this->branch, [
            'store_name' => 'Diego Mega Store',
            'name' => 'Cabang Central',
            'phone' => '089999999',
        ]);

        $this->assertEquals('Diego Mega Store', $updated->store_name);
        $this->assertEquals('Cabang Central', $updated->name);
        $this->assertDatabaseHas('branches', [
            'id' => $this->branch->id,
            'store_name' => 'Diego Mega Store',
        ]);
    }

    /** @test */
    public function it_can_update_receipt_settings()
    {
        $action = new UpdateReceiptSettings();
        $setting = $action->execute($this->branch->id, [
            'store_display_name' => 'Diego Music Store Central',
            'header_text' => 'Selamat datang sahabat musik!',
            'footer_text' => 'Terima kasih telah berbelanja!',
            'paper_width' => '58mm',
            'show_logo' => true,
            'show_customer' => true,
        ]);

        $this->assertInstanceOf(ReceiptSetting::class, $setting);
        $this->assertEquals('58mm', $setting->paper_width);
        $this->assertDatabaseHas('receipt_settings', [
            'branch_id' => $this->branch->id,
            'paper_width' => '58mm',
        ]);
    }

    /** @test */
    public function it_generates_code128_svg_barcode()
    {
        $svg = BarcodeHelper::generateCode128Svg('SKU-TEST-123', 200, 50);
        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('</svg>', $svg);
        $this->assertStringContainsString('rect', $svg);
    }

    /** @test */
    public function pos_privileges_component_can_render()
    {
        Livewire::actingAs($this->user)
            ->test(PosPrivileges::class)
            ->assertStatus(200)
            ->assertSee('Setting Privilege User');
    }

    /** @test */
    public function pos_store_profile_component_can_render_and_save()
    {
        Livewire::actingAs($this->user)
            ->test(PosStoreProfile::class)
            ->set('store_name', 'Diego Updated Store')
            ->set('name', 'Main Branch')
            ->call('save')
            ->assertStatus(200);

        $this->assertDatabaseHas('branches', [
            'id' => $this->branch->id,
            'store_name' => 'Diego Updated Store',
        ]);
    }

    /** @test */
    public function pos_receipt_settings_component_can_render_and_save()
    {
        Livewire::actingAs($this->user)
            ->test(PosReceiptSettings::class)
            ->set('paper_width', '80mm')
            ->set('header_text', 'Testing Header')
            ->call('save')
            ->assertStatus(200);

        $this->assertDatabaseHas('receipt_settings', [
            'branch_id' => $this->branch->id,
            'header_text' => 'Testing Header',
        ]);
    }

    /** @test */
    public function pos_barcode_print_component_can_render()
    {
        Livewire::actingAs($this->user)
            ->test(PosBarcodePrint::class)
            ->assertStatus(200)
            ->assertSee('Cetak Barcode Produk');
    }

    /** @test */
    public function it_can_change_barcode_manual_settings()
    {
        Livewire::actingAs($this->user)
            ->test(PosBarcodePrint::class)
            ->set('labelWidth', 50)
            ->set('labelHeight', 30)
            ->set('columns', 2)
            ->call('touchCustom')
            ->assertSet('paperLayout', 'custom')
            ->assertSet('labelWidth', 50)
            ->assertSet('columns', 2);
    }

    /** @test */
    public function it_can_open_product_modal_and_add_all_products()
    {
        Livewire::actingAs($this->user)
            ->test(PosBarcodePrint::class)
            ->call('openProductModal')
            ->assertSet('showProductModal', true)
            ->call('closeProductModal')
            ->assertSet('showProductModal', false)
            ->call('addAllProducts');
    }

    /** @test */
    public function it_stores_sku_and_barcode_and_filters_queue_and_categories()
    {
        $product = Product::create([
            'name' => 'Gitar Fender Stratocaster',
            'type' => 'physical',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Sunburst',
            'sku' => 'SKU-FENDER-01',
            'barcode' => '8899776655',
            'price' => 15000000,
            'is_active' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(PosBarcodePrint::class)
            ->call('addVariant', $variant->id)
            ->assertSet("printQueue.{$variant->id}.sku", 'SKU-FENDER-01')
            ->assertSet("printQueue.{$variant->id}.barcode", '8899776655')
            ->set('queueSearch', 'Fender')
            ->assertSee('Gitar Fender Stratocaster')
            ->call('setCategory', 'Gitar & Bass')
            ->assertSet('activeCategory', 'Gitar & Bass');
    }
}
