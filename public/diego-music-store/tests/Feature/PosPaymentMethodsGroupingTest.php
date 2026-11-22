<?php

namespace Tests\Feature;

use App\Actions\PaymentMethod\CreatePaymentMethod;
use App\Actions\PaymentMethod\UpdatePaymentMethod;
use App\Livewire\PosPaymentMethods;
use App\Models\Account;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PosPaymentMethodsGroupingTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_parent_and_child_payment_methods_via_action(): void
    {
        $accountParent = Account::firstOrCreate(['code' => '1101'], ['name' => 'Bank Induk', 'classification' => 'Asset', 'is_active' => true]);
        $accountChild = Account::firstOrCreate(['code' => '1102'], ['name' => 'Bank BCA', 'classification' => 'Asset', 'is_active' => true]);

        $createAction = app(CreatePaymentMethod::class);

        // Create Parent Group: Debit Card
        $parent = $createAction->execute([
            'name' => 'Debit Card',
            'code' => 'debit-card',
            'account_id' => $accountParent->id,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('payment_methods', [
            'id' => $parent->id,
            'name' => 'Debit Card',
            'code' => 'debit-card',
            'parent_id' => null,
            'account_id' => $accountParent->id,
        ]);

        // Create Child Provider: BCA under Debit Card
        $child = $createAction->execute([
            'name' => 'BCA',
            'code' => 'debit-bca',
            'parent_id' => $parent->id,
            'account_id' => $accountChild->id,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('payment_methods', [
            'id' => $child->id,
            'name' => 'BCA',
            'code' => 'debit-bca',
            'parent_id' => $parent->id,
            'account_id' => $accountChild->id,
        ]);

        $this->assertEquals($parent->id, $child->parent->id);
        $this->assertTrue($parent->children->contains($child));
        $this->assertEquals($accountChild->id, $child->getEffectiveAccountId());
    }

    public function test_child_fallback_account_id_to_parent(): void
    {
        $accountParent = Account::firstOrCreate(['code' => '1101'], ['name' => 'Bank Induk', 'classification' => 'Asset', 'is_active' => true]);

        $parent = PaymentMethod::create([
            'name' => 'Credit Card',
            'code' => 'credit-card',
            'account_id' => $accountParent->id,
            'is_active' => true,
        ]);

        $childWithoutAccount = PaymentMethod::create([
            'name' => 'Visa',
            'code' => 'credit-visa',
            'parent_id' => $parent->id,
            'account_id' => null,
            'is_active' => true,
        ]);

        $this->assertEquals($accountParent->id, $childWithoutAccount->getEffectiveAccountId());
    }

    public function test_pos_payment_methods_livewire_component_creates_child_method(): void
    {
        $ownerRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($ownerRole);
        $parent = PaymentMethod::create([
            'name' => 'E-Wallet',
            'code' => 'e-wallet',
            'is_active' => true,
        ]);

        Livewire::actingAs($user)
            ->test(PosPaymentMethods::class)
            ->call('openCreate')
            ->set('name', 'GoPay')
            ->set('code', 'gopay')
            ->set('parent_id', $parent->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('payment_methods', [
            'name' => 'GoPay',
            'code' => 'gopay',
            'parent_id' => $parent->id,
        ]);
    }

    public function test_cannot_delete_parent_method_if_it_has_children(): void
    {
        $ownerRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($ownerRole);
        $parent = PaymentMethod::create([
            'name' => 'Debit Card',
            'code' => 'debit-card',
            'is_active' => true,
        ]);

        $child = PaymentMethod::create([
            'name' => 'Mandiri',
            'code' => 'debit-mandiri',
            'parent_id' => $parent->id,
            'is_active' => true,
        ]);

        Livewire::actingAs($user)
            ->test(PosPaymentMethods::class)
            ->call('confirmDelete', $parent->id)
            ->call('destroy');

        $this->assertDatabaseHas('payment_methods', ['id' => $parent->id]);
    }

    public function test_qris_has_bca_mandiri_bri_children_and_bca_is_default_selected_in_payment_modal(): void
    {
        $bankBca = Account::firstOrCreate(['code' => '111201001'], ['name' => 'BANK BCA', 'classification' => 'Asset', 'is_active' => true]);
        $bankMandiri = Account::firstOrCreate(['code' => '111201002'], ['name' => 'BANK Mandiri', 'classification' => 'Asset', 'is_active' => true]);
        $bankBri = Account::firstOrCreate(['code' => '111201003'], ['name' => 'BANK BRI', 'classification' => 'Asset', 'is_active' => true]);

        $qris = PaymentMethod::where('code', 'qris')->first();
        if (! $qris) {
            $qris = PaymentMethod::create(['name' => 'QRIS', 'code' => 'qris', 'account_id' => $bankBca->id, 'is_active' => true]);
            PaymentMethod::create(['name' => 'BCA', 'code' => 'qris-bca', 'parent_id' => $qris->id, 'account_id' => $bankBca->id, 'is_active' => true, 'is_default' => true]);
            PaymentMethod::create(['name' => 'Mandiri', 'code' => 'qris-mandiri', 'parent_id' => $qris->id, 'account_id' => $bankMandiri->id, 'is_active' => true, 'is_default' => false]);
            PaymentMethod::create(['name' => 'BRI', 'code' => 'qris-bri', 'parent_id' => $qris->id, 'account_id' => $bankBri->id, 'is_active' => true, 'is_default' => false]);
        }

        $qrisBca = PaymentMethod::where('code', 'qris-bca')->first();
        $qrisMandiri = PaymentMethod::where('code', 'qris-mandiri')->first();
        $qrisBri = PaymentMethod::where('code', 'qris-bri')->first();

        $this->assertNotNull($qrisBca);
        $this->assertNotNull($qrisMandiri);
        $this->assertNotNull($qrisBri);
        $this->assertTrue((bool) $qrisBca->is_default);

        // PaymentModal auto-selects default child ('BCA') when 'qris' is toggled
        $modal = Livewire::test(\App\Livewire\Pos\PaymentModal::class, ['grandTotal' => 100000])
            ->call('togglePaymentMethod', 'qris')
            ->assertSet('paymentSubMethods.qris', 'BCA');

        // Toggle default per parent group does not affect other parent groups
        $debit = PaymentMethod::create(['name' => 'Debit Card', 'code' => 'debit_card', 'account_id' => $bankBca->id, 'is_active' => true]);
        $debitBca = PaymentMethod::create(['name' => 'BCA', 'code' => 'debit-bca', 'parent_id' => $debit->id, 'account_id' => $bankBca->id, 'is_active' => true, 'is_default' => true]);
        $debitMandiri = PaymentMethod::create(['name' => 'Mandiri', 'code' => 'debit-mandiri', 'parent_id' => $debit->id, 'account_id' => $bankMandiri->id, 'is_active' => true, 'is_default' => false]);

        $ownerRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($ownerRole);

        Livewire::actingAs($user)
            ->test(PosPaymentMethods::class)
            ->call('toggleDefault', $qrisMandiri->id)
            ->assertHasNoErrors();

        $this->assertFalse((bool) $qrisBca->fresh()->is_default);
        $this->assertTrue((bool) $qrisMandiri->fresh()->is_default);
        $this->assertTrue((bool) $debitBca->fresh()->is_default);
        $this->assertFalse((bool) $debitMandiri->fresh()->is_default);

        // Checkout routing maps QRIS -> Mandiri to BANK Mandiri COA account & formats as "QRIS - Mandiri"
        $resolvedMandiri = PaymentMethod::resolveForCheckout('qris', 'Mandiri');
        $this->assertSame($bankMandiri->id, $resolvedMandiri->getEffectiveAccountId());
        $this->assertSame('QRIS - Mandiri', $resolvedMandiri->formatCheckoutLabel());

        // Without explicit sub-method, resolves to default child ("QRIS - Mandiri" after toggleDefault above)
        $resolvedDefault = PaymentMethod::resolveForCheckout('qris', null);
        $this->assertSame('QRIS - Mandiri', $resolvedDefault->formatCheckoutLabel());

        $resolvedBri = PaymentMethod::resolveForCheckout('qris', 'BRI');
        $this->assertSame($bankBri->id, $resolvedBri->getEffectiveAccountId());
        $this->assertSame('QRIS - BRI', $resolvedBri->formatCheckoutLabel());
    }
}
