<?php

namespace Tests\Feature;

use App\Actions\Voucher\CreateVoucher;
use App\Actions\Voucher\UpdateVoucher;
use App\Livewire\POS;
use App\Livewire\PosVouchers;
use App\Models\Branch;
use App\Models\CashSession;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PosVoucherManagementAndValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_and_update_voucher_via_action(): void
    {
        $createAction = app(CreateVoucher::class);

        $voucher = $createAction->execute([
            'code' => 'PROMO50K',
            'name' => 'Voucher Diskon Rp 50.000',
            'type' => 'fixed',
            'value' => 50000,
            'min_spend' => 100000,
            'valid_until' => now()->addDays(7)->format('Y-m-d H:i:s'),
            'max_uses' => 10,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('vouchers', [
            'code' => 'PROMO50K',
            'value' => 50000,
            'min_spend' => 100000,
        ]);

        $updateAction = app(UpdateVoucher::class);
        $updateAction->execute($voucher, [
            'code' => 'PROMO50K-UPDATED',
            'name' => 'Voucher Diskon Rp 75.000',
            'type' => 'fixed',
            'value' => 75000,
            'min_spend' => 150000,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('vouchers', [
            'id' => $voucher->id,
            'code' => 'PROMO50K-UPDATED',
            'value' => 75000,
        ]);
    }

    public function test_voucher_validation_rules(): void
    {
        $voucherExpired = Voucher::create([
            'code' => 'EXPIRED',
            'name' => 'Expired Voucher',
            'type' => 'fixed',
            'value' => 20000,
            'min_spend' => 50000,
            'valid_until' => now()->subDay(),
            'is_active' => true,
        ]);

        $errorMessage = null;
        $this->assertFalse($voucherExpired->isValidForSubtotal(100000, $errorMessage));
        $this->assertStringContainsString('kadaluarsa', strtolower($errorMessage));

        $voucherMinSpend = Voucher::create([
            'code' => 'MIN100K',
            'name' => 'Min Spend Voucher',
            'type' => 'fixed',
            'value' => 20000,
            'min_spend' => 100000,
            'is_active' => true,
        ]);

        $errorMessage = null;
        $this->assertFalse($voucherMinSpend->isValidForSubtotal(50000, $errorMessage));
        $this->assertStringContainsString('minimal belanja', strtolower($errorMessage));

        $errorMessage = null;
        $this->assertTrue($voucherMinSpend->isValidForSubtotal(150000, $errorMessage));
    }

    public function test_pos_livewire_validates_and_applies_voucher(): void
    {
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'sales']);

        $user = User::factory()->create();
        $branch = Branch::create(['name' => 'Branch Test', 'code' => 'BR-TEST', 'is_active' => true]);
        $user->branches()->attach($branch->id);

        CashSession::create([
            'branch_id' => $branch->id,
            'user_id' => $user->id,
            'opened_at' => now(),
            'opening_cash' => 100000,
            'status' => 'open',
        ]);

        $voucher = Voucher::create([
            'code' => 'DISC25K',
            'name' => 'Diskon 25 Ribu',
            'type' => 'fixed',
            'value' => 25000,
            'min_spend' => 50000,
            'is_active' => true,
        ]);

        Livewire::actingAs($user)
            ->test(POS::class)
            ->set('cart', [
                1 => [
                    'variant_id' => 1,
                    'name' => 'Gitar Akustik',
                    'emoji' => '🎸',
                    'qty' => 1,
                    'price' => 100000,
                    'discount_amount' => 0,
                ]
            ])
            ->set('voucherCodeInput', 'DISC25K')
            ->call('validateAndApplyVoucher')
            ->assertSet('voucherIsValid', true)
            ->assertSet('paymentAmounts.voucher', 25000)
            ->assertSet('paymentRefs.voucher', 'DISC25K');
    }
}
