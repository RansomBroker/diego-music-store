<?php

namespace Tests\Feature;

use App\Helpers\AccountHelper;
use App\Models\Account;
use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountHelperTest extends TestCase
{
    use RefreshDatabase;

    public function test_cash_and_bank_query_includes_nine_digit_and_legacy_cash_bank_and_excludes_non_liquid_and_headers(): void
    {
        $branch = Branch::create(['name' => 'Cabang Utama', 'is_active' => true]);

        Account::create(['branch_id' => $branch->id, 'code' => '111100000', 'name' => 'KAS (Header)', 'classification' => 'asset', 'is_header' => true, 'is_active' => true]);
        Account::create(['branch_id' => $branch->id, 'code' => '111200000', 'name' => 'BANK (Header)', 'classification' => 'asset', 'is_header' => true, 'is_active' => true]);
        $kas = Account::create(['branch_id' => $branch->id, 'code' => '111101001', 'name' => 'KAS', 'classification' => 'asset', 'is_header' => false, 'is_active' => true]);
        $bca = Account::create(['branch_id' => $branch->id, 'code' => '111201001', 'name' => 'BANK BCA', 'classification' => 'asset', 'is_header' => false, 'is_active' => true]);
        $tokopedia = Account::create(['branch_id' => $branch->id, 'code' => '111202006', 'name' => 'Tokopedia', 'classification' => 'asset', 'account_subtype' => 'settlement', 'is_header' => false, 'is_active' => true]);
        $legacyKas = Account::create(['branch_id' => $branch->id, 'code' => '1-1000', 'name' => 'Kas Legacy', 'classification' => 'asset', 'is_header' => false, 'is_active' => true]);
        $legacyBank = Account::create(['branch_id' => $branch->id, 'code' => '1-1100', 'name' => 'Bank Legacy', 'classification' => 'asset', 'is_header' => false, 'is_active' => true]);
        $kasBankClass = Account::create(['branch_id' => $branch->id, 'code' => '9-9000', 'name' => 'Kas Bank Custom', 'classification' => 'kas-bank', 'is_header' => false, 'is_active' => true]);

        // Must be excluded
        Account::create(['branch_id' => $branch->id, 'code' => '111201006', 'name' => 'Uang Muka Pembelian', 'classification' => 'asset', 'is_header' => false, 'is_active' => true]);
        Account::create(['branch_id' => $branch->id, 'code' => '1-1400', 'name' => 'Uang Muka Legacy', 'classification' => 'asset', 'is_header' => false, 'is_active' => true]);
        Account::create(['branch_id' => $branch->id, 'code' => '1-1200', 'name' => 'Piutang Legacy', 'classification' => 'asset', 'is_header' => false, 'is_active' => true]);
        Account::create(['branch_id' => $branch->id, 'code' => '1-1300', 'name' => 'Persediaan Legacy', 'classification' => 'asset', 'is_header' => false, 'is_active' => true]);
        Account::create(['branch_id' => $branch->id, 'code' => '111301001', 'name' => 'PIUTANG DAGANG', 'classification' => 'asset', 'is_header' => false, 'is_active' => true]);

        $codes = AccountHelper::cashAndBankQuery($branch->id)->pluck('code')->all();

        $this->assertEqualsCanonicalizing([
            $kas->code,
            $bca->code,
            $tokopedia->code,
            $legacyKas->code,
            $legacyBank->code,
            $kasBankClass->code,
        ], $codes);

        // Falls back to existing accounts when another branch has no COA provisioned yet
        $emptyBranch = Branch::create(['name' => 'Cabang Baru', 'is_active' => true]);
        $fallbackCodes = AccountHelper::cashAndBankQuery($emptyBranch->id)->pluck('code')->all();
        $this->assertContains('111101001', $fallbackCodes);
        $this->assertNotContains('111201006', $fallbackCodes);
    }

    public function test_bank_accounts_query_includes_banks_and_marketplaces_and_excludes_cash_and_advances(): void
    {
        $branch = Branch::create(['name' => 'Cabang Bank', 'is_active' => true]);

        Account::create(['branch_id' => $branch->id, 'code' => '111101001', 'name' => 'KAS', 'classification' => 'asset', 'is_header' => false, 'is_active' => true]);
        Account::create(['branch_id' => $branch->id, 'code' => '111200000', 'name' => 'BANK', 'classification' => 'asset', 'is_header' => true, 'is_active' => true]);
        Account::create(['branch_id' => $branch->id, 'code' => '111201001', 'name' => 'BANK BCA', 'classification' => 'asset', 'is_header' => false, 'is_active' => true]);
        Account::create(['branch_id' => $branch->id, 'code' => '111202006', 'name' => 'Tokopedia', 'classification' => 'asset', 'account_subtype' => 'settlement', 'is_header' => false, 'is_active' => true]);
        Account::create(['branch_id' => $branch->id, 'code' => '111202007', 'name' => 'Shopee', 'classification' => 'asset', 'account_subtype' => 'settlement', 'is_header' => false, 'is_active' => true]);
        Account::create(['branch_id' => $branch->id, 'code' => '1-1100', 'name' => 'Bank Legacy', 'classification' => 'asset', 'is_header' => false, 'is_active' => true]);
        Account::create(['branch_id' => $branch->id, 'code' => '111201006', 'name' => 'Uang Muka Pembelian', 'classification' => 'asset', 'is_header' => false, 'is_active' => true]);
        Account::create(['branch_id' => $branch->id, 'code' => '111301001', 'name' => 'PIUTANG DAGANG', 'classification' => 'asset', 'is_header' => false, 'is_active' => true]);

        $codes = AccountHelper::bankAccountsQuery($branch->id)->pluck('code')->all();

        $this->assertEqualsCanonicalizing(['1-1100', '111201001', '111202006', '111202007'], $codes);
    }

    public function test_is_group_debit_normal_respects_contra_accounts_and_six_prefixed_revenues(): void
    {
        // Debit-normal groups (including contra asset & contra cost)
        $this->assertTrue(AccountHelper::isGroupDebitNormal(new Account(['code' => '111101001', 'classification' => 'asset', 'normal_balance' => 'debit'])));
        $this->assertTrue(AccountHelper::isGroupDebitNormal(new Account(['code' => '121201001', 'classification' => 'asset', 'normal_balance' => 'credit'])));
        $this->assertTrue(AccountHelper::isGroupDebitNormal(new Account(['code' => '511501001', 'classification' => 'cost', 'normal_balance' => 'debit'])));
        $this->assertTrue(AccountHelper::isGroupDebitNormal(new Account(['code' => '511301001', 'classification' => 'cost', 'normal_balance' => 'credit'])));
        $this->assertTrue(AccountHelper::isGroupDebitNormal(new Account(['code' => '611101014', 'classification' => 'expense', 'normal_balance' => 'debit'])));

        // Credit-normal groups (including contra equity, contra revenue, and 6xxx revenue)
        $this->assertFalse(AccountHelper::isGroupDebitNormal(new Account(['code' => '211101001', 'classification' => 'liability', 'normal_balance' => 'credit'])));
        $this->assertFalse(AccountHelper::isGroupDebitNormal(new Account(['code' => '311101002', 'classification' => 'equity', 'normal_balance' => 'debit'])));
        $this->assertFalse(AccountHelper::isGroupDebitNormal(new Account(['code' => '411101001', 'classification' => 'revenue', 'normal_balance' => 'credit'])));
        $this->assertFalse(AccountHelper::isGroupDebitNormal(new Account(['code' => '411201001', 'classification' => 'revenue', 'normal_balance' => 'debit'])));
        $this->assertFalse(AccountHelper::isGroupDebitNormal(new Account(['code' => '621101002', 'classification' => 'revenue', 'normal_balance' => 'credit'])));
        $this->assertFalse(AccountHelper::isGroupDebitNormal(new Account(['code' => '631101002', 'classification' => 'revenue', 'normal_balance' => 'credit'])));
        $this->assertFalse(AccountHelper::isGroupDebitNormal(new Account(['code' => '631101004', 'classification' => 'revenue', 'normal_balance' => 'credit'])));
    }
}

