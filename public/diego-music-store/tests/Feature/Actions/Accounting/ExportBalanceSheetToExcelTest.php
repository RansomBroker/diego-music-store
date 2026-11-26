<?php

namespace Tests\Feature\Actions\Accounting;

use App\Actions\Accounting\ExportBalanceSheetToExcel;
use App\Actions\Accounting\GenerateBalanceSheetReport;
use App\Actions\Branch\CreateBranch;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;

class ExportBalanceSheetToExcelTest extends TestCase
{
    use RefreshDatabase;

    public function test_exports_skontro_xlsx_matching_pdf_structure_and_totals(): void
    {
        $branch = CreateBranch::execute([
            'name' => 'Cabang Balance Sheet Excel',
            'store_name' => 'Diego Musik BS',
        ]);

        $cash = Account::where('branch_id', $branch->id)->where('code', '111101001')->firstOrFail();
        $modalPemilik = Account::where('branch_id', $branch->id)->where('code', '311101003')->firstOrFail();
        $prive = Account::where('branch_id', $branch->id)->where('code', '311101002')->firstOrFail();
        $payable = Account::where('branch_id', $branch->id)->where('code', '211101001')->firstOrFail();

        $entry = JournalEntry::create([
            'branch_id' => $branch->id,
            'entry_no' => 'JV-BS-XLSX-01',
            'date' => '2026-11-26',
            'description' => 'Saldo Awal Uji Neraca',
            'status' => 'posted',
            'posted_at' => now(),
        ]);

        JournalItem::create(['journal_entry_id' => $entry->id, 'account_id' => $cash->id, 'debit' => 10_000_000, 'credit' => 0]);
        JournalItem::create(['journal_entry_id' => $entry->id, 'account_id' => $prive->id, 'debit' => 1_000_000, 'credit' => 0]);
        JournalItem::create(['journal_entry_id' => $entry->id, 'account_id' => $modalPemilik->id, 'debit' => 0, 'credit' => 9_000_000]);
        JournalItem::create(['journal_entry_id' => $entry->id, 'account_id' => $payable->id, 'debit' => 0, 'credit' => 2_000_000]);

        $reportData = (new GenerateBalanceSheetReport())->execute('2026-11-26', $branch->id, 'detail', 'all');
        $response = app(ExportBalanceSheetToExcel::class)->execute($reportData);

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $this->assertStringContainsString('Laporan-Balance-Sheet-2026-11-26.xlsx', (string) $response->headers->get('content-disposition'));

        $filePath = $response->getFile()->getPathname();
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();

        $this->assertSame('DIEGO MUSIC STORE', $sheet->getCell('A1')->getValue());
        $this->assertSame('LAPORAN BALANCE SHEET (NERACA - SKONTRO)', $sheet->getCell('A2')->getValue());
        $this->assertStringContainsString('Cabang Balance Sheet Excel', (string) $sheet->getCell('A3')->getValue());
        $this->assertStringContainsString('NERACA SEIMBANG (BALANCED)', (string) $sheet->getCell('A4')->getValue());

        // Skontro side-by-side section headers
        $this->assertSame('ASET (AKTIVA)', $sheet->getCell('A6')->getValue());
        $this->assertSame('LIABILITAS & EKUITAS (PASIVA)', $sheet->getCell('E6')->getValue());
        $this->assertSame('Aset Lancar', $sheet->getCell('A7')->getValue());
        $this->assertSame('Kewajiban Jangka Pendek', $sheet->getCell('E7')->getValue());

        $rows = $sheet->toArray(null, true, false, true);
        $flatLeft = collect($rows)->pluck('A')->filter()->values()->all();
        $flatRight = collect($rows)->pluck('E')->filter()->values()->all();
        $flatRightNames = collect($rows)->pluck('F')->filter()->map(fn ($v) => trim((string) $v))->values()->all();

        $this->assertContains('Total Aset Lancar', $flatLeft);
        $this->assertContains('TOTAL ASET (AKTIVA)', $flatLeft);
        $this->assertContains('Total Kewajiban Jangka Pendek', $flatRight);
        $this->assertContains('TOTAL KEWAJIBAN', $flatRight);
        $this->assertContains('Ekuitas', $flatRight);
        $this->assertContains('TOTAL EKUITAS', $flatRight);
        $this->assertContains('TOTAL LIABILITAS & EKUITAS', $flatRight);

        // Equity details under MODAL + current net income
        $this->assertContains('MODAL', $flatRightNames);
        $this->assertContains('PRIVE', $flatRightNames);
        $this->assertContains('LABA DITAHAN', $flatRightNames);
        $this->assertContains('LABA TAHUN BERJALAN', $flatRightNames);
        $this->assertContains('Laba / (Rugi) Periode Berjalan', $flatRightNames);

        // Find aligned final total row and check balances
        $totalRowIndex = collect($rows)->search(fn ($r) => ($r['A'] ?? null) === 'TOTAL ASET (AKTIVA)');
        $this->assertNotFalse($totalRowIndex);
        $this->assertSame('TOTAL LIABILITAS & EKUITAS', $rows[$totalRowIndex]['E']);
        $this->assertEquals(10_000_000.0, (float) $rows[$totalRowIndex]['C']);
        $this->assertEquals(10_000_000.0, (float) $rows[$totalRowIndex]['G']);

        // Signature row matching PDF
        $this->assertContains('Disiapkan Oleh (Staf Akuntansi)', $flatLeft);
        $this->assertContains('Diperiksa Oleh (Manager Finance)', collect($rows)->pluck('C')->filter()->values()->all());
        $this->assertContains('Disetujui Oleh (Owner)', collect($rows)->pluck('F')->filter()->values()->all());
    }
}

