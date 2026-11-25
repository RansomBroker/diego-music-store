<?php

namespace Tests\Feature\Actions;

use App\Actions\Procurement\EvaluatePoFinancialHealth;
use App\Actions\Settings\UpdatePoSmartAssistSettings;
use App\Models\Account;
use App\Models\PoSmartAssistSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EvaluatePoFinancialHealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_evaluate_po_financial_health_returns_safe_status_for_small_po(): void
    {
        // Seed liquid cash account
        $account = Account::create([
            'code' => '1-1101',
            'name' => 'Kas Utama',
            'classification' => 'kas-bank',
            'is_active' => true,
            'is_header' => false,
        ]);

        $entry = \App\Models\JournalEntry::create([
            'entry_no' => 'JE-INIT-01',
            'date' => now(),
            'status' => 'posted',
        ]);

        \App\Models\JournalItem::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $account->id,
            'debit' => 50000000,
            'credit' => 0,
        ]);

        $action = new EvaluatePoFinancialHealth();
        $result = $action->execute(poGrandTotal: 1000000, paymentTerm: '30 Hari');

        $this->assertTrue($result['is_enabled']);
        $this->assertEquals('AMAN', $result['status']);
        $this->assertEquals('SAFE', $result['risk_level']);
    }

    public function test_evaluate_po_financial_health_returns_unsafe_status_when_cash_deficit_occurs(): void
    {
        Account::create([
            'code' => '1-1101',
            'name' => 'Kas Utama',
            'classification' => 'kas-bank',
            'is_active' => true,
            'is_header' => false,
        ]);

        // PO nominal exceeds liquid cash
        $action = new EvaluatePoFinancialHealth();
        $result = $action->execute(poGrandTotal: 50000000, paymentTerm: 'COD');

        $this->assertTrue($result['is_enabled']);
        $this->assertEquals('TIDAK_AMAN', $result['status']);
        $this->assertEquals('CRITICAL', $result['risk_level']);
        $this->assertNotEmpty($result['tips']);
    }

    public function test_update_po_smart_assist_settings_action(): void
    {
        $action = new UpdatePoSmartAssistSettings();

        $setting = $action->execute([
            'is_enabled' => true,
            'min_buffer_percentage' => 25,
            'min_buffer_nominal' => 10000000,
            'include_pending_pos' => false,
            'include_sales_projection' => true,
            'warning_threshold_days' => 45,
        ]);

        $this->assertInstanceOf(PoSmartAssistSetting::class, $setting);
        $this->assertEquals(25, $setting->min_buffer_percentage);
        $this->assertEquals(10000000, $setting->min_buffer_nominal);
        $this->assertFalse($setting->include_pending_pos);
    }

    public function test_evaluate_po_financial_health_counts_nine_digit_cash_bank_and_excludes_advances_and_headers(): void
    {
        Account::create([
            'code' => '111100000',
            'name' => 'KAS (Header)',
            'classification' => 'asset',
            'is_active' => true,
            'is_header' => true,
        ]);

        $kas = Account::create([
            'code' => '111101001',
            'name' => 'KAS',
            'classification' => 'asset',
            'is_active' => true,
            'is_header' => false,
        ]);

        $bankBca = Account::create([
            'code' => '111201001',
            'name' => 'BANK BCA',
            'classification' => 'asset',
            'is_active' => true,
            'is_header' => false,
        ]);

        $tokopedia = Account::create([
            'code' => '111202006',
            'name' => 'Tokopedia',
            'classification' => 'asset',
            'is_active' => true,
            'is_header' => false,
        ]);

        $advance = Account::create([
            'code' => '111201006',
            'name' => 'Uang Muka Pembelian',
            'classification' => 'asset',
            'is_active' => true,
            'is_header' => false,
        ]);

        $entry = \App\Models\JournalEntry::create([
            'entry_no' => 'JE-9DIGIT-01',
            'date' => now(),
            'status' => 'posted',
        ]);

        \App\Models\JournalItem::create(['journal_entry_id' => $entry->id, 'account_id' => $kas->id, 'debit' => 20000000, 'credit' => 0]);
        \App\Models\JournalItem::create(['journal_entry_id' => $entry->id, 'account_id' => $bankBca->id, 'debit' => 30000000, 'credit' => 0]);
        \App\Models\JournalItem::create(['journal_entry_id' => $entry->id, 'account_id' => $tokopedia->id, 'debit' => 10000000, 'credit' => 0]);
        \App\Models\JournalItem::create(['journal_entry_id' => $entry->id, 'account_id' => $advance->id, 'debit' => 99000000, 'credit' => 0]);

        $result = (new EvaluatePoFinancialHealth())->execute(poGrandTotal: 1000000, paymentTerm: '30 Hari');

        $this->assertSame(60000000.0, (float) $result['total_liquid_cash']);
        $this->assertEquals('AMAN', $result['status']);
    }
}
