<?php

namespace Tests\Feature;

use App\Filament\Resources\PricingTiers\Pages\ListPricingTiers;
use App\Filament\Resources\PricingTiers\PricingTierResource;
use App\Livewire\POS;
use App\Livewire\Pos\PaymentModal;
use App\Models\Branch;
use App\Models\CashSession;
use App\Models\PosHeldTransaction;
use App\Models\PricingTier;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PosPricingTierCensorPricesTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;
    protected ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'bo.access',
            'bo.pricing_tiers.view',
            'bo.pricing_tiers.create',
            'bo.pricing_tiers.edit',
            'fo.access',
            'fo.pos.view',
            'fo.pos.checkout',
            'fo.pos.hold',
            'fo.pos.view_censored_prices',
        ] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $this->branch = Branch::create([
            'name' => 'Cabang Tier Sensor',
            'address' => 'Jl. Musik',
            'phone' => '08123',
            'is_active' => true,
        ]);

        $product = Product::create([
            'name' => 'Gitar Rahasia Tier',
            'type' => 'physical',
            'is_active' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Custom',
            'sku' => 'GTR-TIER-01',
            'price' => 3_500_000,
            'cost_price' => 2_000_000,
            'hpp' => 2_000_000,
        ]);

        ProductBranchStock::create([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $this->variant->id,
            'stock' => 10,
            'hpp' => 2_000_000,
        ]);
    }

    public function test_pricing_tiers_uses_modal_crud_and_saves_censor_prices_flag(): void
    {
        $this->assertArrayHasKey('index', PricingTierResource::getPages());
        $this->assertArrayNotHasKey('create', PricingTierResource::getPages());
        $this->assertArrayNotHasKey('edit', PricingTierResource::getPages());

        $ownerRole = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $owner = User::factory()->create();
        $owner->assignRole($ownerRole);
        $this->actingAs($owner);

        Livewire::test(ListPricingTiers::class)
            ->callAction('create', [
                'name' => 'Grosir Rahasia',
                'description' => 'Harga khusus grosir',
                'price_follows_hpp' => false,
                'censor_prices' => true,
            ])
            ->assertHasNoActionErrors();

        $tier = PricingTier::where('name', 'Grosir Rahasia')->firstOrFail();
        $this->assertTrue((bool) $tier->censor_prices);
    }

    public function test_pos_censors_prices_for_cashier_on_censored_pricing_tier_but_shows_for_owner_and_authorized_role(): void
    {
        $censoredTier = PricingTier::create([
            'name' => 'VIP Sensor',
            'description' => 'Tier tersensor',
            'price_follows_hpp' => false,
            'censor_prices' => true,
        ]);

        // 1. Cashier WITHOUT fo.pos.view_censored_prices -> prices censored as ****
        $cashierRole = Role::firstOrCreate(['name' => 'kasir_tier_censor', 'guard_name' => 'web']);
        $cashierRole->syncPermissions(['fo.access', 'fo.pos.view', 'fo.pos.checkout']);

        $cashier = User::factory()->create();
        $cashier->assignRole($cashierRole);
        $cashier->branches()->attach($this->branch->id);

        CashSession::create([
            'user_id' => $cashier->id,
            'branch_id' => $this->branch->id,
            'opened_at' => now(),
            'opening_cash' => 500_000,
            'status' => 'open',
        ]);

        $this->actingAs($cashier);
        session(['pos_active_branch_id' => $this->branch->id]);

        Livewire::test(POS::class)
            ->set('selectedPricingTierId', $censoredTier->id)
            ->call('addToCart', $this->variant->id)
            ->assertSee('****')
            ->assertDontSee('3.500.000');

        // 2. Owner -> sees real prices despite censor_prices = true
        $ownerRole = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $owner = User::factory()->create();
        $owner->assignRole($ownerRole);
        $owner->branches()->attach($this->branch->id);
        $this->actingAs($owner);

        Livewire::test(POS::class)
            ->set('selectedPricingTierId', $censoredTier->id)
            ->call('addToCart', $this->variant->id)
            ->assertSee('3.500.000')
            ->assertDontSee('****');

        // 3. Cashier WITH fo.pos.view_censored_prices -> sees real prices
        $cashierRole->givePermissionTo('fo.pos.view_censored_prices');
        $this->actingAs($cashier->fresh());

        Livewire::test(POS::class)
            ->set('selectedPricingTierId', $censoredTier->id)
            ->call('addToCart', $this->variant->id)
            ->assertSee('3.500.000')
            ->assertDontSee('****');
    }

    public function test_payment_modal_masks_totals_when_censor_prices_is_enabled(): void
    {
        Livewire::test(PaymentModal::class, [
            'grandTotal' => 3_500_000,
            'subtotal' => 3_500_000,
            'censorPrices' => true,
        ])
            ->assertSee('****')
            ->assertDontSee('3.500.000');
    }

    public function test_held_transactions_modal_censors_price_for_cashier_but_shows_for_owner(): void
    {
        $censoredTier = PricingTier::create([
            'name' => 'Grosir Hold Sensor',
            'price_follows_hpp' => false,
            'censor_prices' => true,
        ]);

        $cashierRole = Role::firstOrCreate(['name' => 'kasir_hold_censor', 'guard_name' => 'web']);
        $cashierRole->syncPermissions(['fo.access', 'fo.pos.view', 'fo.pos.hold']);

        $cashier = User::factory()->create();
        $cashier->assignRole($cashierRole);
        $cashier->branches()->attach($this->branch->id);

        CashSession::create([
            'user_id' => $cashier->id,
            'branch_id' => $this->branch->id,
            'opened_at' => now(),
            'opening_cash' => 500_000,
            'status' => 'open',
        ]);

        PosHeldTransaction::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'branch_id' => $this->branch->id,
            'user_id' => $cashier->id,
            'customer_name' => 'Pelanggan Hold Rahasia',
            'pricing_tier_id' => $censoredTier->id,
            'cart_data' => [
                [
                    'id' => $this->variant->id,
                    'name' => 'Gitar Rahasia Tier',
                    'price' => 3_500_000,
                    'qty' => 1,
                    'pricing_tier_id' => $censoredTier->id,
                ],
            ],
        ]);

        $this->actingAs($cashier);
        session(['pos_active_branch_id' => $this->branch->id]);

        Livewire::test(POS::class)
            ->set('showHeldModal', true)
            ->assertSee('Pelanggan Hold Rahasia')
            ->assertSee('****')
            ->assertDontSee('3.500.000');

        $cashierRole->givePermissionTo('fo.pos.view_censored_prices');
        $this->actingAs($cashier->fresh());

        Livewire::test(POS::class)
            ->set('showHeldModal', true)
            ->assertSee('Pelanggan Hold Rahasia')
            ->assertSee('3.500.000');
    }
}

