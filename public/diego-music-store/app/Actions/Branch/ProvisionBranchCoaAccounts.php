<?php

namespace App\Actions\Branch;

use App\Models\Account;
use App\Models\AccountClassification;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ProvisionBranchCoaAccounts
{
    /**
     * Map of sub-types to their Level 2 sub-header definitions.
     */
    private const SUBTYPE_HEADERS_MAP = [
        'cash' => ['code' => '111100000', 'name' => 'KAS'],
        'bank' => ['code' => '111200000', 'name' => 'BANK'],
        'purchase_advance' => ['code' => '111300000', 'name' => 'UANG MUKA PEMBELIAN'],
        'current_other' => ['code' => '111400000', 'name' => 'ASET LANCAR LAINNYA'],
        'settlement' => ['code' => '111500000', 'name' => 'PENAMPUNGAN / SETTLEMENT'],
        'receivable' => ['code' => '111600000', 'name' => 'PIUTANG USAHA'],
        'receivable_employee' => ['code' => '111700000', 'name' => 'PIUTANG KARYAWAN'],
        'inventory' => ['code' => '111800000', 'name' => 'PERSEDIAAN'],
        'employee_advance' => ['code' => '111900000', 'name' => 'PERSEKOT KARYAWAN'],
        'other_receivable' => ['code' => '112000000', 'name' => 'PIUTANG LAIN-LAIN'],
        'prepaid_expense' => ['code' => '112100000', 'name' => 'BIAYA DIBAYAR DIMUKA'],
        'tax_receivable' => ['code' => '112200000', 'name' => 'PAJAK DIBAYAR DIMUKA'],
        'fixed_asset' => ['code' => '121100000', 'name' => 'ASET TETAP'],
        'accumulated_depreciation' => ['code' => '121200000', 'name' => 'AKUMULASI PENYUSUTAN'],
        'intangible_asset' => ['code' => '122100000', 'name' => 'ASET TIDAK BERWUJUD'],
        'accumulated_amortization' => ['code' => '122200000', 'name' => 'AKUMULASI AMORTISASI'],

        'trade_payable' => ['code' => '211100000', 'name' => 'HUTANG DAGANG'],
        'tax_payable' => ['code' => '211200000', 'name' => 'HUTANG PAJAK'],
        'other_payable' => ['code' => '211300000', 'name' => 'HUTANG LAIN-LAIN'],
        'long_term_liability' => ['code' => '221100000', 'name' => 'HUTANG JANGKA PANJANG'],

        'paid_in_capital' => ['code' => '311100000', 'name' => 'MODAL'],
        'owner_withdrawal' => ['code' => '311200000', 'name' => 'PRIVE'],
        'retained_earnings' => ['code' => '311300000', 'name' => 'LABA DITAHAN'],
        'current_year_earnings' => ['code' => '311400000', 'name' => 'LABA BERJALAN'],

        'sales' => ['code' => '411100000', 'name' => 'PENJUALAN'],
        'sales_return' => ['code' => '411200000', 'name' => 'RETUR PENJUALAN'],
        'sales_discount' => ['code' => '411300000', 'name' => 'POTONGAN PENJUALAN'],

        'purchase' => ['code' => '511100000', 'name' => 'PEMBELIAN'],
        'purchase_freight' => ['code' => '511200000', 'name' => 'BIAYA ANGKUT PEMBELIAN'],
        'purchase_return' => ['code' => '511300000', 'name' => 'RETUR PEMBELIAN'],
        'purchase_discount' => ['code' => '511400000', 'name' => 'POTONGAN PEMBELIAN'],
        'cost_of_goods_sold' => ['code' => '511500000', 'name' => 'HARGA POKOK PENJUALAN'],

        'operating_expense' => ['code' => '611100000', 'name' => 'BEBAN OPERASIONAL'],
        'cash_shortage' => ['code' => '611200000', 'name' => 'SELISIH KAS KURANG'],
        'depreciation' => ['code' => '611300000', 'name' => 'BEBAN PENYUSUTAN'],
        'tax_expense' => ['code' => '611400000', 'name' => 'BEBAN PAJAK'],
        'amortization' => ['code' => '611500000', 'name' => 'BEBAN AMORTISASI'],
        'selling_expense' => ['code' => '611600000', 'name' => 'BEBAN PENJUALAN'],
        'bank_expense' => ['code' => '621100000', 'name' => 'BIAYA ADMINISTRASI BANK'],
        'other_expense' => ['code' => '631100000', 'name' => 'BIAYA LAIN-LAIN'],
        'inventory_adjustment' => ['code' => '631200000', 'name' => 'PENYESUAIAN STOK'],
        'payment_difference' => ['code' => '631300000', 'name' => 'SELISIH PEMBAYARAN'],
        'corporate_tax' => ['code' => '641100000', 'name' => 'PAJAK BADAN'],

        'interest_income' => ['code' => '421100000', 'name' => 'PENDAPATAN BUNGA'],
        'other_income' => ['code' => '421200000', 'name' => 'PENDAPATAN LAIN-LAIN'],
        'cash_overage' => ['code' => '421300000', 'name' => 'SELISIH KAS LEBIH'],
    ];

    /**
     * Provision all 97 official COA accounts for a specific branch.
     *
     * @param Branch $branch
     * @return int Number of accounts created or updated
     */
    public function execute(Branch $branch): int
    {
        return DB::transaction(function () use ($branch) {
            $branchId = $branch->id;

            // Ensure classifications exist
            $classifications = [
                'asset' => 'Aset',
                'liability' => 'Liabilitas',
                'equity' => 'Ekuitas',
                'revenue' => 'Pendapatan',
                'cost' => 'Harga Pokok Penjualan',
                'expense' => 'Beban',
            ];
            foreach ($classifications as $key => $name) {
                AccountClassification::firstOrCreate(
                    ['key' => $key],
                    ['name' => $name]
                );
            }

            // 1. Create Root Level 1 Headers (branch-scoped)
            $headers = [
                '100000000' => ['name' => 'ASET', 'classification' => 'asset'],
                '200000000' => ['name' => 'LIABILITAS', 'classification' => 'liability'],
                '300000000' => ['name' => 'EKUITAS', 'classification' => 'equity'],
                '400000000' => ['name' => 'PENDAPATAN', 'classification' => 'revenue'],
                '500000000' => ['name' => 'HARGA POKOK PENJUALAN', 'classification' => 'cost'],
                '600000000' => ['name' => 'BEBAN OPERASIONAL & LAIN-LAIN', 'classification' => 'expense'],
            ];

            $headerModels = [];
            foreach ($headers as $code => $data) {
                $headerModels[$code] = Account::updateOrCreate(
                    [
                        'code' => $code,
                        'branch_id' => $branchId,
                    ],
                    [
                        'name' => $data['name'],
                        'classification' => $data['classification'],
                        'is_header' => true,
                        'is_active' => true,
                    ]
                );
            }

            // 2. Read official 97 accounts CSV
            $csvPath = database_path('data/coa_diego_music_store_97_akun.csv');
            if (!File::exists($csvPath)) {
                $csvPath = base_path('../../coa_diego_music_store_97_akun.csv');
            }

            if (!File::exists($csvPath)) {
                throw new \RuntimeException("File CSV COA tidak ditemukan di: {$csvPath}");
            }

            $rows = array_map('str_getcsv', file($csvPath));
            array_shift($rows); // Remove CSV header

            $level2Models = [];
            $accountCount = 0;

            foreach ($rows as $row) {
                if (empty($row) || count($row) < 6) {
                    continue;
                }

                $code = trim($row[1]);
                $name = trim($row[2]);
                $type = trim($row[3]);
                $subtype = trim($row[4]);
                $normalBalance = trim($row[5]);

                // Determine Root Parent (Level 1)
                $firstDigit = substr($code, 0, 1);
                $rootCode = $firstDigit . '00000000';
                $rootParent = $headerModels[$rootCode] ?? null;

                // Determine Sub-Header (Level 2)
                $subHeaderDef = self::SUBTYPE_HEADERS_MAP[$subtype] ?? null;
                $level2Parent = null;

                if ($subHeaderDef) {
                    $subCode = $subHeaderDef['code'];
                    if (!isset($level2Models[$subCode])) {
                        $level2Models[$subCode] = Account::updateOrCreate(
                            [
                                'code' => $subCode,
                                'branch_id' => $branchId,
                            ],
                            [
                                'name' => $subHeaderDef['name'],
                                'classification' => $type,
                                'account_subtype' => $subtype,
                                'is_header' => true,
                                'parent_id' => $rootParent?->id,
                                'is_active' => true,
                            ]
                        );
                    }
                    $level2Parent = $level2Models[$subCode];
                }

                $finalParent = $level2Parent ?: $rootParent;

                // Create or update Detail Account (Level 3)
                Account::updateOrCreate(
                    [
                        'code' => $code,
                        'branch_id' => $branchId,
                    ],
                    [
                        'name' => $name,
                        'classification' => $type,
                        'account_subtype' => $subtype,
                        'normal_balance' => $normalBalance,
                        'is_header' => false,
                        'parent_id' => $finalParent?->id,
                        'is_active' => true,
                    ]
                );

                $accountCount++;
            }

            // 3. Update branch-specific account pointers
            $inventoryAcc = Account::where('branch_id', $branchId)->where('code', '111401001')->first()
                ?? Account::where('branch_id', $branchId)->where('code', '111800000')->first();

            $receivableAcc = Account::where('branch_id', $branchId)->where('code', '111601001')->first()
                ?? Account::where('branch_id', $branchId)->where('code', '111301001')->first();

            $payableAcc = Account::where('branch_id', $branchId)->where('code', '211601001')->first()
                ?? Account::where('branch_id', $branchId)->where('code', '211101001')->first();

            $branch->update([
                'inventory_account_id' => $inventoryAcc?->id,
                'interbranch_receivable_account_id' => $receivableAcc?->id,
                'interbranch_payable_account_id' => $payableAcc?->id,
            ]);

            return $accountCount;
        });
    }

    /**
     * Provision COA accounts for all active branches in the system.
     *
     * @return int Number of branches provisioned
     */
    public static function executeAll(): int
    {
        $instance = new self();
        $branches = Branch::all();
        $count = 0;

        foreach ($branches as $branch) {
            $instance->execute($branch);
            $count++;
        }

        return $count;
    }
}
