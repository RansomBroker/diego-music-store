<?php

namespace App\Actions\Customer;

use App\Models\Customer;
use App\Models\CustomerLabel;
use App\Models\PricingTier;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportCustomers
{
    /**
     * Required headers for Customer import.
     *
     * @var array<int, string>
     */
    public const REQUIRED_HEADERS = [
        'name',
        'phone',
        'email',
        'address',
        'date_of_birth',
        'customer_label',
        'pricing_tier',
        'is_loyalty_member',
        'loyalty_points',
        'outstanding_debt',
    ];

    /**
     * Execute the action to import customer rows.
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
                    $errors[] = "Baris {$rowNum}: Kolom 'name' (Nama Pelanggan) wajib diisi.";
                    continue;
                }

                $phone = !empty($row['phone']) ? trim((string) $row['phone']) : null;
                $email = !empty($row['email']) ? trim((string) $row['email']) : null;
                $address = !empty($row['address']) ? trim((string) $row['address']) : null;

                // Parse Date of Birth
                $dob = null;
                if (!empty($row['date_of_birth'])) {
                    try {
                        $dob = Carbon::parse($row['date_of_birth'])->format('Y-m-d');
                    } catch (Exception $e) {
                        $dob = null;
                    }
                }

                // Resolve Customer Label
                $customerLabelId = null;
                if (!empty($row['customer_label'])) {
                    $labelName = trim((string) $row['customer_label']);
                    $label = CustomerLabel::firstOrCreate(
                        ['key' => Str::slug($labelName)],
                        ['name' => $labelName]
                    );
                    $customerLabelId = $label->id;
                }

                // Resolve Pricing Tier
                $pricingTierId = null;
                if (!empty($row['pricing_tier'])) {
                    $tierName = trim((string) $row['pricing_tier']);
                    $tier = PricingTier::where('name', 'like', $tierName)->first();
                    if (!$tier) {
                        $tier = PricingTier::firstOrCreate(['name' => $tierName]);
                    }
                    $pricingTierId = $tier->id;
                } else {
                    $defaultTier = PricingTier::first();
                    $pricingTierId = $defaultTier?->id;
                }

                // Parse Loyalty Member & Points
                $isLoyalty = false;
                if (isset($row['is_loyalty_member'])) {
                    $val = strtolower(trim((string) $row['is_loyalty_member']));
                    $isLoyalty = in_array($val, ['1', 'true', 'yes', 'ya', 'y', 'member'], true);
                }

                $loyaltyPoints = isset($row['loyalty_points']) ? max(0, (int) $row['loyalty_points']) : 0;

                // Parse Outstanding Debt
                $rawDebt = (string) ($row['outstanding_debt'] ?? '0');
                $cleanDebt = preg_replace('/[^\d.]/', '', str_replace(',', '.', $rawDebt));
                $outstandingDebt = is_numeric($cleanDebt) ? (float) $cleanDebt : 0.0;

                $customerData = [
                    'name' => $name,
                    'phone' => $phone,
                    'email' => $email,
                    'address' => $address,
                    'date_of_birth' => $dob,
                    'customer_label_id' => $customerLabelId,
                    'pricing_tier_id' => $pricingTierId,
                    'is_loyalty_member' => $isLoyalty,
                    'loyalty_points' => $loyaltyPoints,
                    'outstanding_debt' => $outstandingDebt,
                ];

                // Upsert logic: search by unique phone if provided, otherwise create
                if ($phone) {
                    $existing = Customer::where('phone', $phone)->first();
                    if ($existing) {
                        $existing->update($customerData);
                        $importedCount++;
                        continue;
                    }
                }

                Customer::create($customerData);
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
