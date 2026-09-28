<?php

namespace App\Actions\Supplier;

use App\Helpers\AccountHelper;
use App\Helpers\SpreadsheetImportHelper;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\PurchaseTransaction;
use App\Models\Supplier;
use Exception;
use Illuminate\Support\Facades\DB;

class ImportSupplierDebts
{
    /**
     * Required headers for Supplier Debt import.
     *
     * @var array<int, string>
     */
    public const REQUIRED_HEADERS = [
        'supplier',
        'tanggal',
        'nota',
        'sisa_hutang',
    ];

    /**
     * Execute the action to import supplier debt invoice rows.
     *
     * @param array<int, array<string, mixed>> $rows
     * @param int $branchId
     * @param int|null $userId
     * @param string|null $lastSupplierName Carry-forward supplier name from previous batch
     * @return array{imported: int, skipped: int, errors: array<int, string>, total_debt_value: int, last_supplier: string|null}
     */
    public function execute(
        array $rows,
        int $branchId,
        ?int $userId = null,
        ?string $lastSupplierName = null
    ): array {
        $importedCount = 0;
        $skippedCount = 0;
        $errors = [];
        $batchDebtValue = 0;
        $currentSupplierName = $lastSupplierName;

        DB::transaction(function () use (
            $rows,
            $branchId,
            $userId,
            &$currentSupplierName,
            &$importedCount,
            &$skippedCount,
            &$errors,
            &$batchDebtValue
        ) {
            foreach ($rows as $index => $row) {
                $rowNum = $row['_row_number'] ?? ($index + 2);

                // 1. Detect subtotal / footer rows (e.g. "Total : 1.330.696")
                $isSubtotal = false;
                foreach ($row as $val) {
                    if (is_string($val) && preg_match('/^\s*total\s*:?/i', $val)) {
                        $isSubtotal = true;
                        break;
                    }
                }

                $rawNota = trim((string) ($row['nota'] ?? ''));
                $rawTanggal = trim((string) ($row['tanggal'] ?? ''));

                if ($isSubtotal || ($rawNota === '' && $rawTanggal === '')) {
                    // Skip subtotal or blank separator rows
                    continue;
                }

                // 2. Handle merged / carry-forward supplier name
                $rawSupplier = trim((string) ($row['supplier'] ?? ''));
                if ($rawSupplier !== '') {
                    $currentSupplierName = $rawSupplier;
                }

                if (empty($currentSupplierName)) {
                    $skippedCount++;
                    $errors[] = "Baris {$rowNum}: Nama supplier tidak terdeteksi untuk nota '{$rawNota}'.";
                    continue;
                }

                if ($rawNota === '') {
                    $skippedCount++;
                    $errors[] = "Baris {$rowNum}: Nomor Nota / Faktur wajib diisi untuk supplier {$currentSupplierName}.";
                    continue;
                }

                // 3. Parse date
                $invoiceDate = SpreadsheetImportHelper::parseDate($rawTanggal);

                // 4. Parse debt amount (sisa_hutang or total_hutang)
                $rawAmount = $row['sisa_hutang'] ?? $row['total_hutang'] ?? 0;
                $amount = SpreadsheetImportHelper::parseDebtAmount($rawAmount);

                if ($amount <= 0) {
                    $skippedCount++;
                    $errors[] = "Baris {$rowNum}: Nilai sisa hutang untuk nota '{$rawNota}' harus lebih besar dari 0.";
                    continue;
                }

                $project = trim((string) ($row['project'] ?? 'TOKO'));
                $notes = "Saldo Awal Hutang Supplier" . ($project !== '' ? " - Project: {$project}" : '');

                try {
                    // 5. Find or create master Supplier
                    $supplier = Supplier::firstOrCreate(
                        ['name' => $currentSupplierName],
                        ['outstanding_debt' => 0]
                    );

                    // 6. Check existing PurchaseTransaction by supplier and invoice_number
                    $existingPt = PurchaseTransaction::where('supplier_id', $supplier->id)
                        ->where('invoice_number', $rawNota)
                        ->first();

                    if ($existingPt) {
                        $diff = $amount - $existingPt->grand_total;
                        $existingPt->update([
                            'invoice_date' => $invoiceDate,
                            'transaction_date' => $invoiceDate,
                            'subtotal' => $amount,
                            'grand_total' => $amount,
                            'notes' => $notes,
                        ]);

                        if ($diff != 0) {
                            $supplier->increment('outstanding_debt', $diff);
                            $batchDebtValue += $diff;
                        }

                        $importedCount++;
                        continue;
                    }

                    // 7. Create posted credit PurchaseTransaction
                    $transactionNo = PurchaseTransaction::generateTransactionNo();
                    PurchaseTransaction::create([
                        'transaction_no' => $transactionNo,
                        'transaction_date' => $invoiceDate,
                        'supplier_id' => $supplier->id,
                        'branch_id' => $branchId,
                        'warehouse_id' => $branchId,
                        'purchase_type' => 'Kredit',
                        'invoice_number' => $rawNota,
                        'invoice_date' => $invoiceDate,
                        'subtotal' => $amount,
                        'discount' => 0,
                        'shipping_cost' => 0,
                        'other_cost' => 0,
                        'tax_amount' => 0,
                        'pph_amount' => 0,
                        'grand_total' => $amount,
                        'status' => 'posted',
                        'posted_at' => now(),
                        'created_by' => $userId,
                        'notes' => $notes,
                    ]);

                    // 8. Increment supplier outstanding debt
                    $supplier->increment('outstanding_debt', $amount);
                    $batchDebtValue += $amount;
                    $importedCount++;
                } catch (Exception $e) {
                    $skippedCount++;
                    $errors[] = "Baris {$rowNum} ({$rawNota}): " . $e->getMessage();
                }
            }
        });

        return [
            'imported'         => $importedCount,
            'skipped'          => $skippedCount,
            'errors'           => $errors,
            'total_debt_value' => $batchDebtValue,
            'last_supplier'    => $currentSupplierName,
        ];
    }

    /**
     * Record a balanced double-entry journal for initial supplier debts.
     *
     * Debit: 311101001 - Modal Disetor (or 311201001 - Laba Ditahan)
     * Credit: 211101001 - Hutang Dagang
     *
     * @param int $totalValue Total value of imported unpaid supplier debts.
     * @param int $branchId Target branch for accounting attribution.
     * @param int|null $contraAccountId Specific equity account ID (Modal Disetor or Laba Ditahan).
     * @param int|null $userId User ID performing the action.
     * @param int $invoiceCount Count of invoice lines imported.
     * @return JournalEntry|null
     * @throws Exception
     */
    public function recordInitialDebtJournal(
        int $totalValue,
        int $branchId,
        ?int $contraAccountId = null,
        ?int $userId = null,
        int $invoiceCount = 0
    ): ?JournalEntry {
        if ($totalValue <= 0) {
            return null;
        }

        $apAcc = AccountHelper::findByCode('211101001')
            ?: Account::find(AccountHelper::resolveAccountId('211101001', 'HUTANG DAGANG', 'liability'));

        $contraAcc = $contraAccountId ? Account::find($contraAccountId) : null;
        if (!$contraAcc) {
            $contraAcc = AccountHelper::findByCode('311101001')
                ?: Account::find(AccountHelper::resolveAccountId('311101001', 'MODAL DISETOR', 'equity'));
        }

        if (!$apAcc || !$contraAcc) {
            return null;
        }

        return DB::transaction(function () use ($totalValue, $branchId, $apAcc, $contraAcc, $userId, $invoiceCount) {
            $journal = JournalEntry::create([
                'branch_id' => $branchId,
                'date' => now()->format('Y-m-d'),
                'description' => "Saldo Awal Hutang Supplier dari Import Faktur ({$invoiceCount} faktur)",
                'reference_type' => 'InitialDebt',
                'status' => 'posted',
                'posted_at' => now(),
                'posted_by' => $userId,
                'created_by' => $userId,
            ]);

            // 1. Debit: Modal Disetor / Laba Ditahan (Reducing equity for initial liabilities)
            JournalItem::create([
                'journal_entry_id' => $journal->id,
                'account_id' => $contraAcc->id,
                'debit' => $totalValue,
                'credit' => 0,
                'notes' => "Penyeimbang saldo awal hutang supplier ({$contraAcc->name})",
            ]);

            // 2. Kredit: Hutang Dagang (211101001 - Liability increase)
            JournalItem::create([
                'journal_entry_id' => $journal->id,
                'account_id' => $apAcc->id,
                'debit' => 0,
                'credit' => $totalValue,
                'notes' => "Saldo awal hutang dagang ({$invoiceCount} faktur)",
            ]);

            return $journal;
        });
    }
}
