<?php

namespace Tests\Feature\Reports;

use Illuminate\Support\Facades\View;
use Tests\TestCase;

class FinancialReportNegativeFormattingTest extends TestCase
{
    public function test_pdf_reports_render_negative_amounts_with_parentheses(): void
    {
        $balanceSheetHtml = View::make('reports.balance-sheet-pdf', [
            'data' => [
                'as_of_date' => '2026-11-22',
                'branch_name' => 'Pusat',
                'view_type' => 'summary',
                'is_balanced' => false,
                'difference' => -25000,
                'total_assets' => -100000,
                'total_liabilities' => -40000,
                'total_equity' => -60000,
                'total_liabilities_and_equity' => -75000,
                'assets' => [
                    'current_assets' => [[
                        'code' => '1101',
                        'name' => 'Kas Kecil',
                        'balance' => -100000,
                        'is_header' => false,
                    ]],
                    'total_current_assets' => -100000,
                    'fixed_assets' => [],
                    'total_fixed_assets' => 0,
                ],
                'liabilities' => [
                    'current_liabilities' => [[
                        'code' => '2101',
                        'name' => 'Hutang Dagang',
                        'balance' => -40000,
                        'is_header' => false,
                    ]],
                    'total_current_liabilities' => -40000,
                    'long_term_liabilities' => [],
                    'total_long_term_liabilities' => 0,
                ],
                'equity' => [
                    'items' => [],
                    'current_net_income' => -60000,
                ],
            ],
        ])->render();

        $this->assertStringContainsString('(100.000)', $balanceSheetHtml);
        $this->assertStringContainsString('(Rp 100.000)', $balanceSheetHtml);
        $this->assertStringContainsString('(60.000)', $balanceSheetHtml);
        $this->assertStringContainsString('(Rp 60.000)', $balanceSheetHtml);
        $this->assertStringContainsString('color: #dc2626;', $balanceSheetHtml);
        $this->assertStringContainsString('text-rose-600 dark:text-rose-400', $balanceSheetHtml);
        $this->assertStringNotContainsString('Rp -', $balanceSheetHtml);
        $this->assertStringNotContainsString('>-100.000<', $balanceSheetHtml);

        $trialBalanceHtml = View::make('reports.trial-balance-pdf', [
            'data' => [
                'from_date' => '2026-11-01',
                'to_date' => '2026-11-22',
                'branch_name' => 'Pusat',
                'is_balanced' => false,
                'difference' => -15000,
                'items' => [],
                'total_beginning_debit' => 0,
                'total_beginning_credit' => 0,
                'total_period_debit' => 0,
                'total_period_credit' => 0,
                'total_ending_debit' => 10000,
                'total_ending_credit' => 25000,
            ],
        ])->render();

        $this->assertStringContainsString('(Rp 15.000)', $trialBalanceHtml);
        $this->assertStringNotContainsString('Rp -', $trialBalanceHtml);

        $cashBookHtml = View::make('reports.cash-book-report-pdf', [
            'data' => [
                'from_date' => '2026-11-01',
                'to_date' => '2026-11-22',
                'branch_name' => 'Pusat',
                'mode' => 'detail',
                'account_name' => 'Kas Utama',
                'initial_balance' => -10000,
                'total_inflow' => 0,
                'total_outflow' => 5000,
                'ending_balance' => -15000,
                'categories' => [],
                'rows' => [[
                    'entry_no' => 'JV-01',
                    'date' => '2026-11-10',
                    'account_name' => 'Kas Utama',
                    'opposing_account' => 'Biaya Operasional',
                    'description' => 'Biaya admin',
                    'inflow' => 0,
                    'outflow' => 5000,
                    'running_balance' => -15000,
                ]],
            ],
        ])->render();

        $this->assertStringContainsString('(10.000)', $cashBookHtml);
        $this->assertStringContainsString('(15.000)', $cashBookHtml);
        $this->assertStringContainsString('(Rp 15.000)', $cashBookHtml);
        $this->assertStringNotContainsString('Rp -', $cashBookHtml);

        $generalLedgerHtml = View::make('reports.general-ledger-pdf', [
            'data' => [
                'from_date' => '2026-11-01',
                'to_date' => '2026-11-22',
                'branch_name' => 'Pusat',
                'selected_account' => null,
                'ledgers' => [[
                    'account_code' => '1101',
                    'account_name' => 'Kas',
                    'classification' => 'asset',
                    'beginning_balance' => -20000,
                    'total_debit' => 0,
                    'total_credit' => 10000,
                    'ending_balance' => -30000,
                    'transactions' => [[
                        'date' => '2026-11-10',
                        'entry_no' => 'JV-02',
                        'description' => 'Test',
                        'debit' => 0,
                        'credit' => 10000,
                        'running_balance' => -30000,
                    ]],
                ]],
            ],
        ])->render();

        $this->assertStringContainsString('(20.000)', $generalLedgerHtml);
        $this->assertStringContainsString('(30.000)', $generalLedgerHtml);
        $this->assertStringContainsString('(Rp 30.000)', $generalLedgerHtml);
        $this->assertStringContainsString('color: #dc2626;', $generalLedgerHtml);
        $this->assertStringNotContainsString('Rp -', $generalLedgerHtml);

        $webComponentHtml = View::make('components.financial-amount', [
            'amount' => -31597230,
        ])->render();
        $this->assertStringContainsString('(Rp 31.597.230)', $webComponentHtml);
        $this->assertStringContainsString('text-rose-600 dark:text-rose-400', $webComponentHtml);
        $this->assertStringContainsString('color: #ef4444;', $webComponentHtml);

        $positiveComponentHtml = View::make('components.financial-amount', [
            'amount' => 100208326,
        ])->render();
        $this->assertSame('Rp 100.208.326', trim($positiveComponentHtml));
    }
}

