<?php

namespace Tests\Feature\Actions\FocusProduct;

use App\Actions\FocusProduct\AcceptFocusRecommendation;
use App\Actions\FocusProduct\CreateManualFocusProduct;
use App\Actions\FocusProduct\DismissFocusProduct;
use App\Actions\FocusProduct\DismissRecommendation;
use App\Actions\FocusProduct\ExpireFocusProducts;
use App\Actions\FocusProduct\ResolveFocusProduct;
use App\Models\Branch;
use App\Models\FocusProduct;
use App\Models\FocusProductRecommendation;
use App\Models\FocusProductRule;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\FocusProductRuleSeeder;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FocusProductLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch1;
    protected Branch $branch2;
    protected ProductVariant $variant;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FocusProductRuleSeeder::class);

        $this->user = User::factory()->create();

        $this->branch1 = Branch::create([
            'name' => 'Cabang Pontianak',
            'store_name' => 'Diego Music Pontianak',
            'is_active' => true,
        ]);

        $this->branch2 = Branch::create([
            'name' => 'Cabang Singkawang',
            'store_name' => 'Diego Music Singkawang',
            'is_active' => true,
        ]);

        $product = Product::create([
            'name' => 'Yamaha F310',
            'type' => 'physical',
            'is_active' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'DG-YMH-F310',
            'name' => 'Natural',
            'price' => 1400000,
            'cost_price' => 950000,
            'hpp' => 950000,
        ]);

        ProductBranchStock::create([
            'branch_id' => $this->branch1->id,
            'product_variant_id' => $this->variant->id,
            'stock' => 10,
            'hpp' => 950000,
        ]);
    }

    public function test_can_create_manual_focus_product_and_sets_active(): void
    {
        $action = app(CreateManualFocusProduct::class);
        $focusProduct = $action->execute([
            'branch_id' => $this->branch1->id,
            'product_variant_id' => $this->variant->id,
            'reason' => 'Perlu dorongan promo awal bulan',
            'note' => 'Diskon 10% di kasir',
            'active_until' => now()->addDays(14)->toDateTimeString(),
            'user_id' => $this->user->id,
        ]);

        $this->assertNotNull($focusProduct);
        $this->assertEquals('ACTIVE', $focusProduct->status);
        $this->assertEquals('MANUAL', $focusProduct->source);
        $this->assertStringStartsWith('FP-', $focusProduct->focus_number);
        $this->assertEquals($this->branch1->id, $focusProduct->branch_id);
        $this->assertEquals($this->user->id, $focusProduct->focused_by);
        $this->assertNotNull($focusProduct->active_from);
        $this->assertNotNull($focusProduct->active_until);
    }

    public function test_prevents_duplicate_active_focus_on_same_branch_and_variant(): void
    {
        $action = app(CreateManualFocusProduct::class);

        $action->execute([
            'branch_id' => $this->branch1->id,
            'product_variant_id' => $this->variant->id,
            'reason' => 'Fokus pertama',
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('sudah aktif sebagai Produk Fokus');

        // Attempt second active focus on same branch and variant
        $action->execute([
            'branch_id' => $this->branch1->id,
            'product_variant_id' => $this->variant->id,
            'reason' => 'Fokus kedua duplikat',
        ]);
    }

    public function test_allows_active_focus_on_different_branch_for_same_variant(): void
    {
        $action = app(CreateManualFocusProduct::class);

        // Branch 1
        $focus1 = $action->execute([
            'branch_id' => $this->branch1->id,
            'product_variant_id' => $this->variant->id,
            'reason' => 'Fokus di Pontianak',
        ]);

        // Branch 2
        $focus2 = $action->execute([
            'branch_id' => $this->branch2->id,
            'product_variant_id' => $this->variant->id,
            'reason' => 'Fokus di Singkawang',
        ]);

        $this->assertEquals('ACTIVE', $focus1->status);
        $this->assertEquals('ACTIVE', $focus2->status);
        $this->assertNotEquals($focus1->id, $focus2->id);
    }

    public function test_can_accept_focus_recommendation(): void
    {
        $rule = FocusProductRule::where('code', 'dead_stock')->first();

        $recommendation = FocusProductRecommendation::create([
            'branch_id' => $this->branch1->id,
            'product_variant_id' => $this->variant->id,
            'rule_id' => $rule->id,
            'score' => 90,
            'current_stock' => 5,
            'reason' => 'Stok 5 unit tanpa penjualan 6 bulan',
            'status' => 'PENDING',
            'evaluated_at' => now(),
        ]);

        $action = app(AcceptFocusRecommendation::class);
        $focus = $action->execute(
            $recommendation->id,
            now()->addDays(30)->toDateTimeString(),
            'Target cuci gudang',
            $this->user->id
        );

        $this->assertEquals('ACTIVE', $focus->status);
        $this->assertEquals('RULE', $focus->source);
        $this->assertEquals($rule->id, $focus->rule_id);
        $this->assertEquals($recommendation->id, $focus->recommendation_id);

        $recommendation->refresh();
        $this->assertEquals('ACCEPTED', $recommendation->status);
    }

    public function test_can_resolve_focus_product(): void
    {
        $focus = FocusProduct::create([
            'branch_id' => $this->branch1->id,
            'product_variant_id' => $this->variant->id,
            'source' => 'MANUAL',
            'status' => 'ACTIVE',
            'reason' => 'Fokus penjualan',
            'active_from' => now(),
            'focused_at' => now(),
        ]);

        $action = app(ResolveFocusProduct::class);
        $resolved = $action->execute($focus->id, 'Semua stok berhasil terjual habis.', $this->user->id);

        $this->assertEquals('RESOLVED', $resolved->status);
        $this->assertEquals('Semua stok berhasil terjual habis.', $resolved->resolution_note);
        $this->assertEquals($this->user->id, $resolved->resolved_by);
        $this->assertNotNull($resolved->resolved_at);
    }

    public function test_can_dismiss_focus_product(): void
    {
        $focus = FocusProduct::create([
            'branch_id' => $this->branch1->id,
            'product_variant_id' => $this->variant->id,
            'source' => 'MANUAL',
            'status' => 'ACTIVE',
            'reason' => 'Fokus penjualan',
            'active_from' => now(),
            'focused_at' => now(),
        ]);

        $action = app(DismissFocusProduct::class);
        $dismissed = $action->execute($focus->id, 'Produk memang kelas premium slow-moving, tidak perlu promo.', $this->user->id);

        $this->assertEquals('DISMISSED', $dismissed->status);
        $this->assertEquals('Produk memang kelas premium slow-moving, tidak perlu promo.', $dismissed->dismissal_note);
        $this->assertEquals($this->user->id, $dismissed->dismissed_by);
        $this->assertNotNull($dismissed->dismissed_at);
    }

    public function test_can_dismiss_recommendation(): void
    {
        $rule = FocusProductRule::where('code', 'slow_moving')->first();

        $recommendation = FocusProductRecommendation::create([
            'branch_id' => $this->branch1->id,
            'product_variant_id' => $this->variant->id,
            'rule_id' => $rule->id,
            'score' => 70,
            'current_stock' => 2,
            'reason' => 'Slow moving',
            'status' => 'PENDING',
            'evaluated_at' => now(),
        ]);

        $action = app(DismissRecommendation::class);
        $dismissed = $action->execute($recommendation->id, $this->user->id);

        $this->assertEquals('DISMISSED', $dismissed->status);
        $this->assertEquals($this->user->id, $dismissed->dismissed_by);
        $this->assertNotNull($dismissed->dismissed_at);
    }

    public function test_expires_active_focus_products_whose_until_date_passed(): void
    {
        // 1. Should expire: active_until is yesterday
        $focusExpired = FocusProduct::create([
            'branch_id' => $this->branch1->id,
            'product_variant_id' => $this->variant->id,
            'source' => 'MANUAL',
            'status' => 'ACTIVE',
            'reason' => 'Fokus kemarin',
            'active_from' => now()->subDays(10),
            'active_until' => now()->subDay(),
            'focused_at' => now()->subDays(10),
        ]);

        // 2. Should NOT expire: active_until is next week
        $otherProduct = Product::create(['name' => 'Fender Strat', 'type' => 'physical', 'is_active' => true]);
        $otherVariant = ProductVariant::create(['product_id' => $otherProduct->id, 'sku' => 'DG-FND', 'name' => 'V', 'price' => 1000000, 'cost_price' => 800000, 'hpp' => 800000]);

        $focusStillActive = FocusProduct::create([
            'branch_id' => $this->branch1->id,
            'product_variant_id' => $otherVariant->id,
            'source' => 'MANUAL',
            'status' => 'ACTIVE',
            'reason' => 'Fokus aktif',
            'active_from' => now(),
            'active_until' => now()->addDays(7),
            'focused_at' => now(),
        ]);

        $action = app(ExpireFocusProducts::class);
        $count = $action->execute();

        $this->assertEquals(1, $count);

        $focusExpired->refresh();
        $this->assertEquals('EXPIRED', $focusExpired->status);

        $focusStillActive->refresh();
        $this->assertEquals('ACTIVE', $focusStillActive->status);
    }
}
