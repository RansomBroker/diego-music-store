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
}
