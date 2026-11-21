<?php

namespace Tests\Feature\Actions\Supplier;

use App\Actions\Supplier\ImportSupplierDebts;
use App\Helpers\AccountHelper;
use App\Helpers\SpreadsheetImportHelper;
use App\Models\Account;
use App\Models\Branch;
use App\Models\JournalEntry;
use App\Models\PurchaseTransaction;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportSupplierDebtsTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::firstOrCreate(
            ['name' => 'Cabang Utama'],
            ['address' => 'Jl. Merdeka No. 1', 'phone' => '0561-112233', 'is_active' => true]
        );

        // Ensure accounts exist
        AccountHelper::resolveAccountId('211101001', 'HUTANG DAGANG', 'liability');
        AccountHelper::resolveAccountId('311101001', 'MODAL DISETOR', 'equity');
    }

    public function test_can_import_supplier_debts_with_merged_cells_and_skip_totals(): void
    {
        $rows = [
            // Row 1: First supplier invoice
            [
                'no' => 1,
                'supplier' => 'BORNEO MUSIKA JAYA',
                'tanggal' => '25-07-2026',
                'nota' => 'BMJ/250726',
                'project' => 'TOKO',
                'total_hutang' => '166.89',
                'total_pembayaran' => 0,
                'sisa_hutang' => '166.89',
                '_row_number' => 2,
            ],
            // Row 2: Same supplier, empty supplier cell (carry forward)
            [
                'no' => null,
                'supplier' => null,
                'tanggal' => '27-07-2026',
                'nota' => 'BMJ/270726',
                'project' => 'TOKO',
                'total_hutang' => '163.9',
                'total_pembayaran' => 0,
                'sisa_hutang' => '163.9',
                '_row_number' => 3,
            ],
            // Row 3: Same supplier, thousands dot
            [
                'no' => null,
                'supplier' => '',
                'tanggal' => '28-07-2026',
                'nota' => 'BMJ280726',
                'project' => 'TOKO',
                'total_hutang' => '583.602',
                'total_pembayaran' => 0,
                'sisa_hutang' => '583.602',
                '_row_number' => 4,
            ],
            // Subtotal row (should be skipped)
            [
                'no' => null,
                'supplier' => null,
                'tanggal' => null,
                'nota' => null,
                'project' => null,
                'total_hutang' => 'Total :',
                'total_pembayaran' => '914.392',
                'sisa_hutang' => null,
                '_row_number' => 5,
            ],
            // Row 4: Second supplier with million format
            [
                'no' => 2,
                'supplier' => 'CV ANEKA MUSIKA STUDIO',
                'tanggal' => '25-09-2026',
                'nota' => 'NP/26-00321',
                'project' => 'TOKO',
                'total_hutang' => '40.041.400',
                'total_pembayaran' => 0,
                'sisa_hutang' => '40.041.400',
                '_row_number' => 6,
            ],
        ];

        $action = app(ImportSupplierDebts::class);
        $result = $action->execute($rows, $this->branch->id);

        $this->assertEquals(4, $result['imported']);
        $this->assertEquals(0, $result['skipped']);
        $this->assertEmpty($result['errors']);
        // 166890 + 163900 + 583602 + 40041400 = 40,955,792
        $this->assertEquals(40955792, $result['total_debt_value']);

        // Check Supplier 1
        $supplier1 = Supplier::where('name', 'BORNEO MUSIKA JAYA')->first();
        $this->assertNotNull($supplier1);
        $this->assertEquals(914392, (int) $supplier1->outstanding_debt);

        // Check Supplier 2
        $supplier2 = Supplier::where('name', 'CV ANEKA MUSIKA STUDIO')->first();
        $this->assertNotNull($supplier2);
        $this->assertEquals(40041400, (int) $supplier2->outstanding_debt);

        // Check Purchase Transactions created
        $pt1 = PurchaseTransaction::where('invoice_number', 'BMJ/250726')->first();
        $this->assertNotNull($pt1);
        $this->assertEquals('Kredit', $pt1->purchase_type);
        $this->assertEquals('posted', $pt1->status);
        $this->assertEquals('2026-07-25', $pt1->invoice_date->format('Y-m-d'));
        $this->assertEquals(166890, $pt1->grand_total);
        $this->assertEquals(166890, $pt1->getRemainingUnpaidAmount());

        $pt4 = PurchaseTransaction::where('invoice_number', 'NP/26-00321')->first();
        $this->assertNotNull($pt4);
        $this->assertEquals(40041400, $pt4->grand_total);
        $this->assertEquals(40041400, $pt4->getRemainingUnpaidAmount());
    }

    public function test_can_record_balanced_initial_debt_accounting_journal_option_1(): void
    {
        $totalDebt = 40955792;
        $action = app(ImportSupplierDebts::class);

        $journal = $action->recordInitialDebtJournal(
            totalValue: $totalDebt,
            branchId: $this->branch->id,
            contraAccountId: null,
            userId: null,
            invoiceCount: 4
        );

        $this->assertNotNull($journal);
        $this->assertEquals('InitialDebt', $journal->reference_type);
        $this->assertEquals('posted', $journal->status);
        $this->assertEquals(2, $journal->items()->count());

        // Check Debit Item (Modal Disetor / Ekuitas)
        $debitItem = $journal->items()->where('debit', '>', 0)->first();
        $this->assertNotNull($debitItem);
        $this->assertEquals($totalDebt, $debitItem->debit);
        $this->assertEquals(0, $debitItem->credit);
        $this->assertEquals('311101001', $debitItem->account->code);

        // Check Credit Item (Hutang Dagang)
        $creditItem = $journal->items()->where('credit', '>', 0)->first();
        $this->assertNotNull($creditItem);
        $this->assertEquals($totalDebt, $creditItem->credit);
        $this->assertEquals(0, $creditItem->debit);
        $this->assertEquals('211101001', $creditItem->account->code);
    }

    public function test_parse_debt_amount_handles_all_user_formats_accurately(): void
    {
        // Trailing zero dropped in thousand format (from screenshot)
        $this->assertEquals(166890, SpreadsheetImportHelper::parseDebtAmount('166.89'));
        $this->assertEquals(163900, SpreadsheetImportHelper::parseDebtAmount('163.9'));
        $this->assertEquals(184800, SpreadsheetImportHelper::parseDebtAmount('184.8'));
        $this->assertEquals(583602, SpreadsheetImportHelper::parseDebtAmount('583.602'));
        $this->assertEquals(231504, SpreadsheetImportHelper::parseDebtAmount('231.504'));

        // Indonesian multi-dot millions format
        $this->assertEquals(1330696, SpreadsheetImportHelper::parseDebtAmount('1.330.696'));
        $this->assertEquals(40041400, SpreadsheetImportHelper::parseDebtAmount('40.041.400'));
        $this->assertEquals(1486125, SpreadsheetImportHelper::parseDebtAmount('1.486.125'));
        $this->assertEquals(5999550, SpreadsheetImportHelper::parseDebtAmount('5.999.550'));
        $this->assertEquals(7485675, SpreadsheetImportHelper::parseDebtAmount('7.485.675'));

        // Plain integers or numeric floats
        $this->assertEquals(166890, SpreadsheetImportHelper::parseDebtAmount(166.89));
        $this->assertEquals(5000000, SpreadsheetImportHelper::parseDebtAmount(5000000));
        $this->assertEquals(0, SpreadsheetImportHelper::parseDebtAmount(0));
    }

    public function test_imports_due_date_and_us_slash_dates_with_comma_amounts_and_total_semua(): void
    {
        $tsv = <<<'TSV'
No	Supplier	Tanggal	Jatuh Tempo	Nota	Project	Total Hutang	Total Pembayaran	Sisa Hutang
1	BANG BONG MUSIC	11/5/2026	12/5/2026	OL26110500000788	TOKO	21,600,000	0	21,600,000
					Total : 	21,600,000	
2	BORNEO MUSIKA JAYA	11/16/2026	12/16/2026	BMJ/161126	TOKO	1,079,598	0	1,079,598
					Total : 	1,079,598	
3	CV ANEKA MUSIKA STUDIO	11/14/2026	11/28/2026	NP/26-00390	TOKO	73,440,000	0	73,440,000
					Total : 	73,440,000	
4	MITRA CE	10/6/2026	10/27/2026	158567	TOKO	111,377,000	0	111,377,000
		10/7/2026	10/28/2026	158584	TOKO	210,000	0	210,000
		10/16/2026	11/6/2026	158862	TOKO	8,010,000	0	8,010,000
					Total : 	119,597,000	
5	PT ASIA GLOBAL PERKASA	11/5/2026	12/20/2026	SI.2026.11.00044	TOKO	74,137,500	0	74,137,500
		11/6/2026	12/21/2026	SI.2026.11.00033	TOKO	6,750,000	0	6,750,000
					Total : 	80,887,500	
6	PT INDO CITRA RAYA	6/14/2024	8/13/2024	ICR-20240624	TOKO	9,453,414	0	9,453,414
					Total : 	9,453,414	
7	PT INSTRUMEN MUSIK BERSAMA	12/22/2025	1/21/2026	DO25123636	TOKO	22,196,000	0	22,196,000
					Total : 	22,196,000	
8	PT MEGAH GENTASWARA	9/15/2026	10/15/2026	F126003766	TOKO	165,720,000	0	165,720,000
					Total : 	165,720,000	
						Total Semua	493,973,512
TSV;

        $tempFile = tempnam(sys_get_temp_dir(), 'debt_due_date_') . '.csv';
        file_put_contents($tempFile, $tsv);

        try {
            $inspect = SpreadsheetImportHelper::inspectFile($tempFile, null, ImportSupplierDebts::REQUIRED_HEADERS);
            $this->assertTrue($inspect['validation']['is_valid']);

            $rows = SpreadsheetImportHelper::readRows($tempFile, null, 0, 100, $inspect['header_row'], $inspect['col_map']);
            $result = app(ImportSupplierDebts::class)->execute($rows, $this->branch->id);

            $this->assertSame(11, $result['imported']);
            $this->assertSame(0, $result['skipped']);
            $this->assertSame(493973512, $result['total_debt_value']);

            $bangBong = PurchaseTransaction::where('invoice_number', 'OL26110500000788')->firstOrFail();
            $this->assertSame('2026-11-05', $bangBong->invoice_date->format('Y-m-d'));
            $this->assertSame('2026-12-05', $bangBong->due_date->format('Y-m-d'));
            $this->assertSame(21600000, $bangBong->grand_total);

            $borneo = PurchaseTransaction::where('invoice_number', 'BMJ/161126')->firstOrFail();
            $this->assertSame('2026-11-16', $borneo->invoice_date->format('Y-m-d'));
            $this->assertSame('2026-12-16', $borneo->due_date->format('Y-m-d'));
            $this->assertSame(1079598, $borneo->grand_total);

            $mitra3 = PurchaseTransaction::where('invoice_number', '158862')->firstOrFail();
            $this->assertSame('MITRA CE', $mitra3->supplier->name);
            $this->assertSame('2026-10-16', $mitra3->invoice_date->format('Y-m-d'));
            $this->assertSame('2026-11-06', $mitra3->due_date->format('Y-m-d'));

            // Re-import with updated due_date updates existing invoice cleanly
            $bangBongRow = [[
                'supplier' => 'BANG BONG MUSIC',
                'tanggal' => '11/5/2026',
                'jatuh_tempo' => '12/15/2026',
                'nota' => 'OL26110500000788',
                'total_hutang' => '21,600,000',
                'sisa_hutang' => '21,600,000',
            ]];
            app(ImportSupplierDebts::class)->execute($bangBongRow, $this->branch->id);
            $this->assertSame('2026-12-15', $bangBong->fresh()->due_date->format('Y-m-d'));
        } finally {
            @unlink($tempFile);
        }
    }
}
