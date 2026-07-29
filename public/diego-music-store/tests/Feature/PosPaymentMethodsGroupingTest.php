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
        $user = User::factory()->create();
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
        $user = User::factory()->create();
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
}
