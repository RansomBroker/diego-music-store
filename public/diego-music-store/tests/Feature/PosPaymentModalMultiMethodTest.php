<?php

namespace Tests\Feature;

use App\Livewire\Pos\PaymentModal;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PosPaymentModalMultiMethodTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        PaymentMethod::firstOrCreate(['code' => 'cash'], ['name' => 'Tunai', 'is_active' => true]);
        PaymentMethod::firstOrCreate(['code' => 'debit'], ['name' => 'Debit', 'is_active' => true]);
        PaymentMethod::firstOrCreate(['code' => 'qris'], ['name' => 'QRIS', 'is_active' => true]);
        PaymentMethod::firstOrCreate(['code' => 'credit'], ['name' => 'Piutang', 'is_active' => true]);
    }

    public function test_selecting_primary_method_replaces_instead_of_adding_and_secondary_button_enforces_max_two(): void
    {
        Livewire::test(PaymentModal::class, ['grandTotal' => 250_000])
            ->assertSet('selectedPaymentMethods', ['cash'])
            ->assertSet('amountCash', 250_000)
            ->assertSee('Tambah Pembayaran Lainnya')
            // Switching primary method replaces 'cash' with 'qris' (no accidental split!)
            ->call('selectPrimaryPaymentMethod', 'qris')
            ->assertSet('selectedPaymentMethods', ['qris'])
            ->assertSet('amountCash', 0)
            ->assertSet('paymentAmounts.qris', 250_000)
            ->assertSee('Tambah Pembayaran Lainnya')
            // Explicitly adding 2nd payment method enables split payment (max 2)
            ->call('addSecondaryPaymentMethod')
            ->assertSet('selectedPaymentMethods', ['qris', 'cash'])
            ->assertDontSee('Tambah Pembayaran Lainnya')
            ->assertSee('Hapus Metode 2')
            // Changing 2nd payment method replaces only slot 2
            ->call('selectSecondaryPaymentMethod', 'debit')
            ->assertSet('selectedPaymentMethods', ['qris', 'debit'])
            // Attempting to add a 3rd method is blocked (remains 2)
            ->call('addSecondaryPaymentMethod', 'credit')
            ->assertSet('selectedPaymentMethods', ['qris', 'debit'])
            // Removing 2nd payment method restores slot 1 to full grandTotal
            ->call('removeSecondaryPaymentMethod')
            ->assertSet('selectedPaymentMethods', ['qris'])
            ->assertSet('paymentAmounts.qris', 250_000)
            ->assertSee('Tambah Pembayaran Lainnya');
    }
}

