<?php

namespace Tests\Feature;

use App\Livewire\POSTransactions;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\Sale;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PosPaymentMethodsSeederAndSettlementTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_seeds_all_9_parent_payment_methods_and_children(): void
    {
        $this->seed(DatabaseSeeder::class);

        $expectedParents = [
            'cash',
            'debit_card',
            'credit_card',
            'entertain',
            'credit',
            'voucher',
            'qris',
            'transfer',
            'other_payment',
        ];

        foreach ($expectedParents as $code) {
            $this->assertDatabaseHas('payment_methods', [
                'code' => $code,
                'parent_id' => null,
            ]);
        }

        // Verify Debit Card children
        $debitCard = PaymentMethod::where('code', 'debit_card')->first();
        $this->assertNotNull($debitCard);
        $this->assertTrue($debitCard->children()->where('code', 'debit-bca')->exists());
        $this->assertTrue($debitCard->children()->where('code', 'debit-bni')->exists());

        // Verify Transfer children
        $transfer = PaymentMethod::where('code', 'transfer')->first();
        $this->assertNotNull($transfer);
        $this->assertTrue($transfer->children()->where('code', 'transfer-bca')->exists());
    }

    public function test_can_process_customer_ar_piutang_settlement_on_pos_transactions(): void
    {
        $user = User::factory()->create();
        $branch = \App\Models\Branch::create(['name' => 'Branch Test', 'code' => 'BR-TEST', 'is_active' => true]);
        $customer = Customer::create(['name' => 'Budi Piutang', 'code' => 'CUST-PIUTANG-01']);

        $sale = Sale::create([
            'branch_id' => $branch->id,
            'created_by' => $user->id,
            'invoice_number' => 'INV-TEST-AR-001',
            'invoice_date' => now()->format('Y-m-d'),
            'customer_id' => $customer->id,
            'sales_rep_id' => $user->id,
            'payment_method' => 'Piutang',
            'payment_status' => 'unpaid',
            'subtotal' => 500000,
            'grand_total' => 500000,
            'status' => 'completed',
        ]);

        Livewire::actingAs($user)
            ->test(POSTransactions::class)
            ->call('openSettlementModal', $sale->id)
            ->set('settlementPaymentMethod', 'transfer-bca')
            ->set('settlementAmount', 500000)
            ->set('settlementNote', 'Transfer Bank BCA #88123')
            ->call('processSettlement')
            ->assertHasNoErrors();

        $sale->refresh();
        $this->assertStringContainsString('Lunas via TRANSFER-BCA', $sale->payment_method);
        $this->assertEquals('completed', $sale->status);
    }
}
