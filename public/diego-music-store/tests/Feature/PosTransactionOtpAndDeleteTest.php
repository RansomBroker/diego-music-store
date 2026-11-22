<?php

namespace Tests\Feature;

use App\Actions\PosOtp\GeneratePosActionOtp;
use App\Actions\Sales\UpdatePOSSale;
use App\Filament\Pages\Settings\PosActionOtpPage;
use App\Helpers\PermissionHelper;
use App\Livewire\POS;
use App\Livewire\POSTransactions;
use App\Models\Account;
use App\Models\Branch;
use App\Models\CashSession;
use App\Models\Employee;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\PosActionOtpLog;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesCommissionLog;
use App\Models\SalesReturn;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class PosTransactionOtpAndDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;
    protected User $owner;
    protected User $cashier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        PermissionHelper::ensureAllPermissionsExist();

        $ownerRole = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $cashierRole = Role::firstOrCreate(['name' => 'kasir', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'sales', 'guard_name' => 'web']);

        $cashierRole->syncPermissions([
            Permission::findByName('fo.access', 'web'),
            Permission::findByName('fo.pos.view', 'web'),
            Permission::findByName('fo.pos.edit_sale', 'web'),
            Permission::findByName('fo.transactions.view', 'web'),
            Permission::findByName('fo.transactions.delete', 'web'),
            Permission::findByName('bo.access', 'web'),
        ]);

        $this->branch = Branch::create([
            'name' => 'Cabang Pusat',
            'address' => 'Jl. Test',
            'phone' => '08123456789',
            'is_active' => true,
        ]);

        $this->owner = User::factory()->create(['name' => 'Owner Utama', 'is_active' => true]);
        $this->owner->assignRole($ownerRole);
        $this->owner->branches()->attach($this->branch);

        $this->cashier = User::factory()->create(['name' => 'Kasir Satu', 'is_active' => true]);
        $this->cashier->assignRole($cashierRole);
        $this->cashier->branches()->attach($this->branch);

        session(['active_branch_id' => $this->branch->id]);
    }

    private function createSaleWithStockJournalAndCommission(string $invoice = 'INV-TEST-001'): array
    {
        $cashAcc = Account::firstOrCreate(['code' => '111101001'], ['name' => 'KAS', 'classification' => 'Asset', 'is_active' => true]);
        $salesAcc = Account::firstOrCreate(['code' => '411101001'], ['name' => 'PENJUALAN', 'classification' => 'Revenue', 'is_active' => true]);

        $product = Product::create([
            'name' => 'Gitar Akustik',
            'type' => 'physical',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'GTR-' . rand(100, 999),
            'name' => 'Natural',
            'price' => 1000000,
            'cost_price' => 600000,
            'hpp' => 600000,
            'is_active' => true,
        ]);

        $branchStock = ProductBranchStock::create([
            'product_variant_id' => $variant->id,
            'branch_id' => $this->branch->id,
            'stock' => 8,
            'hpp' => 600000,
        ]);

        $sale = Sale::create([
            'branch_id' => $this->branch->id,
            'sales_rep_id' => $this->owner->id,
            'invoice_number' => $invoice,
            'invoice_date' => now()->toDateString(),
            'subtotal' => 2000000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 2000000,
            'paid_amount' => 2000000,
            'change_amount' => 0,
            'payment_method' => 'Tunai',
            'status' => 'completed',
            'created_by' => $this->owner->id,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'unit_price' => 1000000,
            'discount_amount' => 0,
            'total_price' => 2000000,
        ]);

        StockMovement::create([
            'product_variant_id' => $variant->id,
            'branch_id' => $this->branch->id,
            'type' => 'out',
            'quantity' => 2,
            'unit_cost' => 600000,
            'hpp' => 600000,
            'reference_type' => 'POS',
            'reference_id' => $sale->id,
        ]);

        $je = JournalEntry::create([
            'branch_id' => $this->branch->id,
            'entry_no' => 'JV-POS-TEST-' . $sale->id,
            'date' => now()->toDateString(),
            'description' => "Penjualan Kasir POS: {$sale->invoice_number}",
            'reference_type' => 'Sales',
            'reference_id' => $sale->id,
            'status' => 'posted',
            'created_by' => $this->owner->id,
            'posted_at' => now(),
            'posted_by' => $this->owner->id,
        ]);

        JournalItem::create([
            'journal_entry_id' => $je->id,
            'account_id' => $cashAcc->id,
            'debit' => 2000000,
            'credit' => 0,
            'notes' => 'Penerimaan POS - Tunai',
        ]);

        JournalItem::create([
            'journal_entry_id' => $je->id,
            'account_id' => $salesAcc->id,
            'debit' => 0,
            'credit' => 2000000,
            'notes' => 'Pendapatan Penjualan POS',
        ]);

        $employee = Employee::firstOrCreate(
            ['user_id' => $this->owner->id],
            [
                'branch_id' => $this->branch->id,
                'nik' => 'EMP-OWNER-001',
                'name' => 'Owner Utama',
                'is_active' => true,
            ]
        );

        SalesCommissionLog::create([
            'employee_id' => $employee->id,
            'sale_id' => $sale->id,
            'date' => now()->toDateString(),
            'sale_amount' => 2000000,
            'commission_amount' => 50000,
            'status' => 'pending',
        ]);

        return [$sale, $branchStock, $je];
    }

    /** @test */
    public function owner_can_access_backoffice_otp_page_and_refresh_6_digit_1_minute_otp(): void
    {
        $this->actingAs($this->owner);

        $component = Livewire::test(PosActionOtpPage::class);
        $firstCode = $component->get('currentCode');

        $this->assertMatchesRegularExpression('/^\d{6}$/', $firstCode);
        $this->assertGreaterThan(now()->timestamp, $component->get('expiresAtTimestamp'));
        $this->assertLessThanOrEqual(now()->addSeconds(65)->timestamp, $component->get('expiresAtTimestamp'));

        $component->call('refreshOtp');
        $secondCode = $component->get('currentCode');

        $this->assertMatchesRegularExpression('/^\d{6}$/', $secondCode);
        $this->assertDatabaseHas('pos_action_otps', [
            'code' => $firstCode,
        ]);
        $this->assertNotNull(\App\Models\PosActionOtp::where('code', $firstCode)->first()->revoked_at);
    }

    /** @test */
    public function non_owner_without_permission_cannot_access_backoffice_otp_page_but_authorized_role_can(): void
    {
        $this->actingAs($this->cashier);

        $this->assertFalse(PosActionOtpPage::canAccess());
        Livewire::test(PosActionOtpPage::class)->assertForbidden();

        // Beri izin melihat saja (tanpa refresh)
        $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $managerRole->syncPermissions([
            Permission::findByName('bo.access', 'web'),
            Permission::findByName('bo.pos_action_otp.view', 'web'),
        ]);
        $manager = User::factory()->create(['name' => 'Manager Toko', 'is_active' => true]);
        $manager->assignRole($managerRole);
        $manager->branches()->attach($this->branch);

        $this->actingAs($manager);
        $this->assertTrue(PosActionOtpPage::canAccess());
        Livewire::test(PosActionOtpPage::class)
            ->assertOk()
            ->call('refreshOtp')
            ->assertForbidden();

        // Beri izin refresh juga
        $managerRole->givePermissionTo(Permission::findByName('bo.pos_action_otp.refresh', 'web'));
        $manager->refresh();
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $component = Livewire::test(PosActionOtpPage::class)->assertOk();
        $oldCode = $component->get('currentCode');
        $component->call('refreshOtp')->assertOk();
        $this->assertNotNull(\App\Models\PosActionOtp::where('code', $oldCode)->first()->revoked_at);
    }

    /** @test */
    public function owner_can_edit_and_hard_delete_sale_without_otp_and_reverts_stock_journal_and_commission(): void
    {
        $this->actingAs($this->owner);
        [$sale, $branchStock, $je] = $this->createSaleWithStockJournalAndCommission('INV-OWNER-01');

        // Edit langsung redirect tanpa modal OTP
        Livewire::test(POSTransactions::class)
            ->call('requestEditSale', $sale->id)
            ->assertSet('showOtpActionModal', false)
            ->assertRedirect('/pos?edit=' . $sale->id);

        // Hapus langsung lewat konfirmasi tanpa OTP
        Livewire::test(POSTransactions::class)
            ->call('confirmDeleteSale', $sale->id)
            ->assertSet('showOtpActionModal', true)
            ->assertSet('otpActionType', 'delete_sale')
            ->call('submitOtpAction')
            ->assertSet('showOtpActionModal', false);

        $this->assertDatabaseMissing('sales', ['id' => $sale->id]);
        $this->assertDatabaseMissing('sale_items', ['sale_id' => $sale->id]);
        $this->assertDatabaseMissing('stock_movements', ['reference_type' => 'POS', 'reference_id' => $sale->id]);
        $this->assertDatabaseMissing('journal_entries', ['id' => $je->id]);
        $this->assertDatabaseMissing('sales_commission_logs', ['sale_id' => $sale->id]);
        $this->assertSame(10, (int) $branchStock->fresh()->stock);
    }

    /** @test */
    public function non_owner_must_verify_otp_for_edit_and_cannot_bypass_via_direct_url_or_action(): void
    {
        $this->actingAs($this->cashier);
        [$sale] = $this->createSaleWithStockJournalAndCommission('INV-EDIT-OTP-01');

        CashSession::create([
            'user_id' => $this->cashier->id,
            'branch_id' => $this->branch->id,
            'opening_cash' => 500000,
            'expected_cash' => 500000,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        // Direct URL /pos?edit={id} tanpa OTP harus redirect ke /pos/transactions
        $this->get('/pos?edit=' . $sale->id)->assertRedirect('/pos/transactions');

        // Direct UpdatePOSSale tanpa OTP harus 403
        try {
            app(UpdatePOSSale::class)->execute($sale, ['items' => []]);
            $this->fail('Expected 403 when updating sale without OTP session.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        $otp = app(GeneratePosActionOtp::class)->execute($this->owner, true);

        // Klik Edit memunculkan modal OTP; OTP salah ditolak; OTP benar redirect & log tercatat
        Livewire::test(POSTransactions::class)
            ->call('requestEditSale', $sale->id)
            ->assertSet('showOtpActionModal', true)
            ->assertSet('otpActionType', 'edit_sale')
            ->set('otpCode', '000000')
            ->call('submitOtpAction')
            ->assertSet('showOtpActionModal', true)
            ->set('otpCode', $otp->code)
            ->call('submitOtpAction')
            ->assertRedirect('/pos?edit=' . $sale->id);

        $this->assertDatabaseHas('pos_action_otp_logs', [
            'user_id' => $this->cashier->id,
            'action' => 'edit_sale',
            'sale_id' => $sale->id,
            'invoice_number' => 'INV-EDIT-OTP-01',
        ]);

        // Setelah verifikasi OTP, /pos?edit={id} dapat dibuka
        $this->get('/pos?edit=' . $sale->id)->assertOk();
    }

    /** @test */
    public function non_owner_must_verify_otp_to_delete_sale_and_expired_or_revoked_otp_is_rejected(): void
    {
        $this->actingAs($this->cashier);
        [$sale, $branchStock] = $this->createSaleWithStockJournalAndCommission('INV-DEL-OTP-01');

        $oldOtp = app(GeneratePosActionOtp::class)->execute($this->owner, true);
        // Refresh manual mencabut OTP lama
        $newOtp = app(GeneratePosActionOtp::class)->execute($this->owner, true);

        // OTP lama yang sudah di-revoke harus ditolak
        Livewire::test(POSTransactions::class)
            ->call('confirmDeleteSale', $sale->id)
            ->set('otpCode', $oldOtp->code)
            ->call('submitOtpAction')
            ->assertSet('showOtpActionModal', true);
        $this->assertDatabaseHas('sales', ['id' => $sale->id]);

        // Simulasikan OTP kedaluwarsa (> 1 menit)
        $newOtp->update(['expires_at' => now()->subSecond()]);
        Livewire::test(POSTransactions::class)
            ->call('confirmDeleteSale', $sale->id)
            ->set('otpCode', $newOtp->code)
            ->call('submitOtpAction')
            ->assertSet('showOtpActionModal', true);
        $this->assertDatabaseHas('sales', ['id' => $sale->id]);

        // OTP aktif baru berhasil menghapus transaksi
        $freshOtp = app(GeneratePosActionOtp::class)->execute($this->owner, true);
        Livewire::test(POSTransactions::class)
            ->call('confirmDeleteSale', $sale->id)
            ->set('otpCode', $freshOtp->code)
            ->call('submitOtpAction')
            ->assertSet('showOtpActionModal', false);

        $this->assertDatabaseMissing('sales', ['id' => $sale->id]);
        $this->assertSame(10, (int) $branchStock->fresh()->stock);
        $this->assertSame(1, PosActionOtpLog::where('action', 'delete_sale')->where('sale_id', $sale->id)->count());
    }

    /** @test */
    public function delete_sale_is_blocked_when_sale_has_return_or_receivable_settlement(): void
    {
        $this->actingAs($this->owner);

        // 1. Transaksi dengan Retur Penjualan
        [$saleWithReturn] = $this->createSaleWithStockJournalAndCommission('INV-RET-BLOCK');
        SalesReturn::create([
            'branch_id' => $this->branch->id,
            'sale_id' => $saleWithReturn->id,
            'return_number' => 'RET-001',
            'return_date' => now()->toDateString(),
            'total_refund' => 1000000,
            'status' => 'posted',
            'created_by' => $this->owner->id,
        ]);

        Livewire::test(POSTransactions::class)
            ->call('confirmDeleteSale', $saleWithReturn->id)
            ->call('submitOtpAction')
            ->assertSet('showOtpActionModal', true);
        $this->assertDatabaseHas('sales', ['id' => $saleWithReturn->id]);

        // 2. Transaksi dengan Pelunasan Piutang
        [$saleWithSettlement] = $this->createSaleWithStockJournalAndCommission('INV-AR-BLOCK');
        JournalEntry::create([
            'branch_id' => $this->branch->id,
            'entry_no' => 'JV-AR-BLOCK-1',
            'date' => now()->toDateString(),
            'description' => "Pelunasan Piutang Inv #{$saleWithSettlement->invoice_number}",
            'reference_type' => 'Sales',
            'reference_id' => $saleWithSettlement->id,
            'status' => 'posted',
            'created_by' => $this->owner->id,
        ]);

        Livewire::test(POSTransactions::class)
            ->call('confirmDeleteSale', $saleWithSettlement->id)
            ->call('submitOtpAction')
            ->assertSet('showOtpActionModal', true);
        $this->assertDatabaseHas('sales', ['id' => $saleWithSettlement->id]);
    }
}
