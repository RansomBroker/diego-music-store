<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ReceiptSetting;
use App\Models\ProductBranchStock;
use App\Actions\Branch\CreateBranch;
use App\Actions\Branch\UpdateBranch;
use App\Livewire\POSLogin;
use App\Livewire\PosBranches;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiBranchManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_branch_action_initializes_access_receipt_settings_and_zero_stocks()
    {
        $admin = User::create([
            'name'     => 'Admin Test',
            'username' => 'admintest',
            'email'    => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        $product = Product::create([
            'name'      => 'Gitar Akustik Test',
            'type'      => 'physical',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku'        => 'SKU-BR-01',
            'price'      => 1000000,
            'hpp'        => 700000,
            'is_active'  => true,
        ]);

        $branch = CreateBranch::execute([
            'name'       => 'Cabang Jakarta Selatan',
            'store_name' => 'Diego Music Jaksel',
            'city'       => 'Jakarta',
            'user_ids'   => [$admin->id],
        ]);

        $this->assertDatabaseHas('branches', [
            'id'   => $branch->id,
            'name' => 'Cabang Jakarta Selatan',
        ]);

        // Check receipt settings creation
        $this->assertDatabaseHas('receipt_settings', [
            'branch_id' => $branch->id,
        ]);

        // Check initial zero stock record creation
        $this->assertDatabaseHas('product_branch_stocks', [
            'branch_id'          => $branch->id,
            'product_variant_id' => $variant->id,
            'stock'              => 0,
        ]);
    }

    public function test_pos_login_multi_branch_selection_flow()
    {
        $b1 = Branch::create(['name' => 'Cabang Pusat', 'store_name' => 'Diego Pusat']);
        $b2 = Branch::create(['name' => 'Cabang Siantan', 'store_name' => 'Diego Siantan']);

        $user = User::create([
            'name'     => 'Kasir Multi',
            'username' => 'kasirmulti',
            'email'    => 'kasirmulti@test.com',
            'password' => bcrypt('password'),
        ]);

        $user->branches()->attach([$b1->id, $b2->id]);

        Livewire::test(POSLogin::class)
            ->set('email', 'kasirmulti')
            ->set('password', 'password')
            ->call('login')
            ->assertSet('showBranchSelectStep', true)
            ->set('selectedBranchId', $b2->id)
            ->call('selectBranchAndCompleteLogin')
            ->assertRedirect('/pos');

        $this->assertEquals($b2->id, session('pos_active_branch_id'));
    }

    public function test_pos_branches_component_renders_and_creates_branch()
    {
        $user = User::create([
            'name'     => 'Owner User',
            'username' => 'owneruser',
            'email'    => 'owneruser@test.com',
            'password' => bcrypt('password'),
        ]);

        Livewire::actingAs($user)
            ->test(PosBranches::class)
            ->assertStatus(200)
            ->set('name', 'Cabang Bandung')
            ->set('store_name', 'Diego Music Bandung')
            ->set('city', 'Bandung')
            ->call('save');

        $this->assertDatabaseHas('branches', [
            'name' => 'Cabang Bandung',
            'city' => 'Bandung',
        ]);
    }

    public function test_filament_branch_resource_creates_branch()
    {
        $admin = User::create([
            'name'     => 'Super Admin',
            'username' => 'superadmin',
            'email'    => 'superadmin@test.com',
            'password' => bcrypt('password'),
        ]);

        $branch = CreateBranch::execute([
            'name'       => 'Cabang Surabaya',
            'store_name' => 'Diego Surabaya',
            'city'       => 'Surabaya',
            'users'      => [$admin->id],
        ]);

        $this->assertDatabaseHas('branches', ['name' => 'Cabang Surabaya']);
        $this->assertDatabaseHas('receipt_settings', ['branch_id' => $branch->id]);
    }

    public function test_branch_helper_enforces_strict_branch_scoping_for_staff()
    {
        $b1 = Branch::create(['name' => 'Cabang Utama', 'store_name' => 'Diego Utama']);
        $b2 = Branch::create(['name' => 'Cabang Bali', 'store_name' => 'Diego Bali']);

        $staff = User::create([
            'name'     => 'Staff Kasir',
            'username' => 'staffkasir',
            'email'    => 'staffkasir@test.com',
            'password' => bcrypt('password'),
        ]);

        $staff->branches()->attach($b2->id);

        $this->actingAs($staff);

        // Try to access b1 which staff doesn't belong to -> BranchHelper should fallback to b2 (assigned branch)
        session(['pos_active_branch_id' => $b1->id]);
        $activeBranchId = \App\Helpers\BranchHelper::getActiveBranchId();

        $this->assertEquals($b2->id, $activeBranchId);
    }

    public function test_filament_resource_applies_strict_branch_scoping()
    {
        $b1 = Branch::create(['name' => 'Cabang 1', 'store_name' => 'Store 1']);
        $b2 = Branch::create(['name' => 'Cabang 2', 'store_name' => 'Store 2']);

        $staff = User::create([
            'name'     => 'Staff 2',
            'username' => 'staff2',
            'email'    => 'staff2@test.com',
            'password' => bcrypt('password'),
        ]);
        $staff->branches()->attach($b2->id);

        $customer = \App\Models\Customer::create(['name' => 'Pelanggan Test']);

        \App\Models\SalesInvoice::create([
            'branch_id'      => $b1->id,
            'customer_id'    => $customer->id,
            'invoice_number' => 'INV-B1-001',
            'invoice_date'   => now()->toDateString(),
            'total_amount'   => 500000,
        ]);

        \App\Models\SalesInvoice::create([
            'branch_id'      => $b2->id,
            'customer_id'    => $customer->id,
            'invoice_number' => 'INV-B2-001',
            'invoice_date'   => now()->toDateString(),
            'total_amount'   => 750000,
        ]);

        $this->actingAs($staff);

        $query = \App\Filament\Resources\SalesInvoices\SalesInvoiceResource::getEloquentQuery();
        $invoices = $query->get();

        $this->assertCount(1, $invoices);
        $this->assertEquals('INV-B2-001', $invoices->first()->invoice_number);
    }
}
