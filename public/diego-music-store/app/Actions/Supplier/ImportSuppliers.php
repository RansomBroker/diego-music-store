<?php

namespace App\Actions\Supplier;

use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportSuppliers
{
    /**
     * Required headers for Supplier import (NO | NAMA | ALAMAT | STATUS).
     *
     * @var array<int, string>
     */
    public const REQUIRED_HEADERS = [
        'nama',
        'alamat',
        'status',
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

        DB::transaction(function () use ($rows, &$importedCount, &$skippedCount, &$errors): void {
            foreach ($rows as $index => $row) {
                $rowNum = $row['_row_number'] ?? ($index + 2);
                $name = trim((string) ($row['nama'] ?? $row['name'] ?? ''));
                $address = trim((string) ($row['alamat'] ?? $row['address'] ?? ''));
                $rawStatus = trim((string) ($row['status'] ?? $row['tipe'] ?? $row['type'] ?? ''));
                $phone = array_key_exists('phone', $row) && trim((string) ($row['phone'] ?? '')) !== ''
                    ? trim((string) $row['phone'])
                    : null;

                // Ignore completely blank rows silently
                if ($name === '' && $address === '' && $rawStatus === '' && $phone === null) {
                    continue;
                }

                if ($name === '') {
                    $skippedCount++;
                    $errors[] = "Baris {$rowNum}: Kolom 'NAMA' (Nama Supplier) wajib diisi.";
                    continue;
                }

                $status = $rawStatus !== ''
                    ? Str::upper(preg_replace('/\s+/', ' ', $rawStatus) ?? $rawStatus)
                    : Supplier::STATUS_SUPPLIER;

                $addressValue = $address !== '' ? $address : null;

                // Check existing supplier by phone (when provided) or case-insensitive name
                $existing = null;
                if ($phone !== null) {
                    $existing = Supplier::where('phone', $phone)->first();
                }
                if (! $existing) {
                    $existing = Supplier::whereRaw('LOWER(name) = ?', [Str::lower($name)])->first();
                }

                $supplierData = [
                    'name' => $name,
                    'status' => $status,
                ];

                if (! $existing || $addressValue !== null) {
                    $supplierData['address'] = $addressValue;
                }

                foreach (['contact_person', 'phone', 'email', 'bank_name', 'bank_account_number', 'bank_account_name'] as $optionalField) {
                    if (array_key_exists($optionalField, $row)) {
                        $val = trim((string) ($row[$optionalField] ?? ''));
                        $supplierData[$optionalField] = $val !== '' ? $val : null;
                    }
                }

                if (array_key_exists('outstanding_debt', $row) && $row['outstanding_debt'] !== null && trim((string) $row['outstanding_debt']) !== '') {
                    $rawDebt = (string) $row['outstanding_debt'];
                    $cleanDebt = preg_replace('/[^\d.]/', '', str_replace(',', '.', $rawDebt));
                    if (is_numeric($cleanDebt)) {
                        $supplierData['outstanding_debt'] = (float) $cleanDebt;
                    }
                } elseif (! $existing) {
                    $supplierData['outstanding_debt'] = 0.0;
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
