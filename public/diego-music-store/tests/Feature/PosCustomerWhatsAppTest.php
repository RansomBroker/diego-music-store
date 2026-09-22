<?php

namespace Tests\Feature;

use App\Actions\Notification\BroadcastWhatsAppMessage;
use App\Actions\Notification\SendWhatsAppBillingReminder;
use App\Livewire\PosCustomers;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\CustomerLabel;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PosCustomerWhatsAppTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;
    protected Customer $customerWithDebt;
    protected Customer $customerLoyalty;
    protected Customer $customerRegular;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);

        $this->user = User::factory()->create();
        $this->user->assignRole('admin');

        $this->branch = Branch::create([
            'name' => 'Cabang Utama',
            'store_name' => 'Diego Music Store',
            'phone' => '081234567890',
            'address' => 'Jl. Pemuda No. 45',
            'fonnte_token' => 'mock-fonnte-token-pos',
            'fonnte_whatsapp_number' => '081234567890',
            'is_whatsapp_enabled' => true,
            'is_active' => true,
        ]);

        $this->user->branches()->attach($this->branch->id);

        $this->customerWithDebt = Customer::create([
            'name' => 'Ahmad Kasir',
            'phone' => '081299990001',
            'email' => 'ahmad@example.com',
            'is_loyalty_member' => false,
            'loyalty_points' => 0,
        ]);

        // Create an unpaid sale to generate piutang
        Sale::create([
            'invoice_number' => 'INV-20260922-001',
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customerWithDebt->id,
            'sales_rep_id' => $this->user->id,
            'created_by' => $this->user->id,
            'user_id' => $this->user->id,
            'payment_method' => 'piutang',
            'subtotal' => 500000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'grand_total' => 500000,
            'status' => 'pending',
            'invoice_date' => now(),
        ]);

        $this->customerLoyalty = Customer::create([
            'name' => 'Dewi Sartika',
            'phone' => '081299990002',
            'email' => 'dewi@example.com',
            'is_loyalty_member' => true,
            'loyalty_points' => 150,
        ]);

        $this->customerRegular = Customer::create([
            'name' => 'Joko Widodo',
            'phone' => '081299990003',
            'email' => 'joko@example.com',
            'is_loyalty_member' => false,
            'loyalty_points' => 0,
        ]);
    }

    public function test_send_whatsapp_billing_reminder_success(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => true,
                'target' => ['081299990001'],
                'message' => 'Pesan terkirim',
            ], 200),
        ]);

        $action = new SendWhatsAppBillingReminder();
        $result = $action->execute($this->customerWithDebt, $this->branch);

        $this->assertTrue($result['success']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.fonnte.com/send'
                && str_contains($request['message'], 'PEMBERITAHUAN TAGIHAN')
                && str_contains($request['message'], 'Ahmad Kasir')
                && str_contains($request['message'], '500.000');
        });
    }

    public function test_send_whatsapp_billing_reminder_fails_when_no_phone(): void
    {
        $customerNoPhone = Customer::create([
            'name' => 'Tanpa Telepon',
            'phone' => null,
        ]);

        $action = new SendWhatsAppBillingReminder();
        $result = $action->execute($customerNoPhone, $this->branch);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('tidak memiliki nomor telepon', $result['message']);
    }

    public function test_send_whatsapp_billing_reminder_fails_when_no_debt(): void
    {
        $action = new SendWhatsAppBillingReminder();
        $result = $action->execute($this->customerLoyalty, $this->branch);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('tidak memiliki saldo piutang', $result['message']);
    }

    public function test_broadcast_whatsapp_message_to_all_recipients(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => true,
                'target' => ['6281299990001'],
            ], 200),
        ]);

        $action = new BroadcastWhatsAppMessage();
        $result = $action->execute(
            "Halo {nama}, selamat datang di {toko}!",
            'all',
            [],
            $this->branch
        );

        $this->assertTrue($result['success']);
        $this->assertEquals(3, $result['total_recipients']);
        $this->assertEquals(3, $result['sent_count']);
        $this->assertEquals(0, $result['failed_count']);

        Http::assertSentCount(3);
    }

    public function test_broadcast_whatsapp_message_filtered_by_loyalty_members(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => true,
                'target' => ['6281299990002'],
            ], 200),
        ]);

        $action = new BroadcastWhatsAppMessage();
        $result = $action->execute(
            "Halo Member {nama}, poin Anda adalah {poin}!",
            'loyalty_members',
            [],
            $this->branch
        );

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['total_recipients']);
        $this->assertEquals(1, $result['sent_count']);

        Http::assertSent(function ($request) {
            return str_contains($request['message'], 'Dewi Sartika')
                && str_contains($request['message'], '150');
        });
    }

    public function test_livewire_pos_customers_broadcast_flow(): void
    {
        $this->actingAs($this->user);

        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => true,
                'target' => ['6281299990002'],
            ], 200),
        ]);

        Livewire::test(PosCustomers::class)
            ->call('openBroadcastModal')
            ->assertSet('showBroadcastModal', true)
            ->call('applyTemplate', 'promo')
            ->assertSet('broadcastTarget', 'all')
            ->set('broadcastTarget', 'loyalty_members')
            ->call('sendBroadcast')
            ->assertSet('showBroadcastModal', false)
            ->assertDispatched('toast');
    }

    public function test_livewire_pos_customers_billing_modal_flow(): void
    {
        $this->actingAs($this->user);

        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => true,
                'target' => ['6281299990001'],
            ], 200),
        ]);

        Livewire::test(PosCustomers::class)
            ->call('openBillingModal', $this->customerWithDebt->id)
            ->assertSet('showBillingModal', true)
            ->assertSee('Ahmad Kasir')
            ->assertSee('500.000')
            ->call('sendBillingReminder')
            ->assertSet('showBillingModal', false)
            ->assertDispatched('toast');
    }

    public function test_livewire_pos_customers_single_customer_message_flow(): void
    {
        $this->actingAs($this->user);

        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => true,
                'target' => ['6281299990002'],
            ], 200),
        ]);

        Livewire::test(PosCustomers::class)
            ->call('openCustomerMessageModal', $this->customerLoyalty->id)
            ->assertSet('showCustomerMessageModal', true)
            ->assertSee('Dewi Sartika')
            ->call('applyCustomerTemplate', 'member')
            ->call('sendCustomerMessage')
            ->assertSet('showCustomerMessageModal', false)
            ->assertDispatched('toast');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.fonnte.com/send'
                && str_contains($request['message'], 'Dewi Sartika')
                && str_contains($request['message'], 'Poin Loyalitas');
        });
    }
}
