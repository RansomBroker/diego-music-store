<?php

namespace App\Actions\Supplier;

use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class ImportSuppliers
{
    /**
     * Required headers for Supplier import.
     *
     * @var array<int, string>
     */
    public const REQUIRED_HEADERS = [
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'outstanding_debt',
    ];

    /**
     * Execute the action to import supplier rows.
     *
     * @param array<int, array<string, mixed>> $rows
     * @return array{imported: int, skipped: int, errors: array<int, string>}
     */
    public function execute(array $rows): array
    {
        $importedCount = 0;
        $skippedCount = 0;
        $errors = [];

        DB::transaction(function () use ($rows, &$importedCount, &$skippedCount, &$errors) {
            foreach ($rows as $index => $row) {
                $rowNum = $row['_row_number'] ?? ($index + 2);
                $name = trim((string) ($row['name'] ?? ''));

                if ($name === '') {
                    $skippedCount++;
                    $errors[] = "Baris {$rowNum}: Kolom 'name' (Nama Supplier) wajib diisi.";
                    continue;
                }

                $contactPerson = !empty($row['contact_person']) ? trim((string) $row['contact_person']) : null;
                $phone = !empty($row['phone']) ? trim((string) $row['phone']) : null;
                $email = !empty($row['email']) ? trim((string) $row['email']) : null;
                $address = !empty($row['address']) ? trim((string) $row['address']) : null;
                $bankName = !empty($row['bank_name']) ? trim((string) $row['bank_name']) : null;
                $bankAccountNumber = !empty($row['bank_account_number']) ? trim((string) $row['bank_account_number']) : null;
                $bankAccountName = !empty($row['bank_account_name']) ? trim((string) $row['bank_account_name']) : null;

                // Parse Outstanding Debt
                $rawDebt = (string) ($row['outstanding_debt'] ?? '0');
                $cleanDebt = preg_replace('/[^\d.]/', '', str_replace(',', '.', $rawDebt));
                $outstandingDebt = is_numeric($cleanDebt) ? (float) $cleanDebt : 0.0;

                $supplierData = [
                    'name' => $name,
                    'contact_person' => $contactPerson,
                    'phone' => $phone,
                    'email' => $email,
                    'address' => $address,
                    'bank_name' => $bankName,
                    'bank_account_number' => $bankAccountNumber,
                    'bank_account_name' => $bankAccountName,
                    'outstanding_debt' => $outstandingDebt,
                ];

                // Check existing supplier by phone or name
                $existing = null;
                if ($phone) {
                    $existing = Supplier::where('phone', $phone)->first();
                }
                if (!$existing) {
                    $existing = Supplier::where('name', $name)->first();
                }

                if ($existing) {
                    $existing->update($supplierData);
                    $importedCount++;
                    continue;
                }

                Supplier::create($supplierData);
                $importedCount++;
            }
        });

        return [
            'imported' => $importedCount,
            'skipped' => $skippedCount,
            'errors' => $errors,
        ];
    }
}
