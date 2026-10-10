<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountClassification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan master klasifikasi tersedia dan label UI konsisten sebelum akun dibuat.
        $classifications = [
            'asset' => 'Aset',
            'liability' => 'Liabilitas',
            'equity' => 'Ekuitas',
            'revenue' => 'Pendapatan',
            'cost' => 'Harga Pokok Penjualan',
            'expense' => 'Beban',
        ];

        foreach ($classifications as $key => $name) {
            AccountClassification::updateOrCreate(
                ['key' => $key],
                ['name' => $name],
            );
        }

        // 1. Definisikan Root Headers Level 1 (9-digit)
        $headers = [
            '100000000' => ['name' => 'ASET', 'classification' => 'asset'],
            '200000000' => ['name' => 'LIABILITAS', 'classification' => 'liability'],
            '300000000' => ['name' => 'EKUITAS', 'classification' => 'equity'],
            '400000000' => ['name' => 'PENDAPATAN', 'classification' => 'revenue'],
            '500000000' => ['name' => 'HARGA POKOK PENJUALAN', 'classification' => 'cost'],
            '600000000' => ['name' => 'BEBAN OPERASIONAL & LAIN-LAIN', 'classification' => 'expense'],
        ];

        // Migrasi kode header lama ke kode baru in-place (agar ID tidak berubah)
        $legacyHeaderMap = [
            '1-0000' => '100000000',
            '2-0000' => '200000000',
            '3-0000' => '300000000',
            '4-0000' => '400000000',
            '5-0000' => '500000000',
            '6-0000' => '600000000',
        ];

        foreach ($legacyHeaderMap as $oldCode => $newCode) {
            $oldHeader = Account::where('code', $oldCode)->first();
            if ($oldHeader && !Account::where('code', $newCode)->exists()) {
                $oldHeader->update(['code' => $newCode]);
            }
        }

        $headerModels = [];
        foreach ($headers as $code => $data) {
            $headerModels[$code] = Account::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $data['name'],
                    'classification' => $data['classification'],
                    'is_header' => true,
                    'is_active' => true,
                ]
            );
        }

        // 2. Migrasi akun detail lama ke kode baru in-place (menjaga ID agar relasi transaksi & payment method tidak rusak)
        $legacyDetailMap = [
            '1-1000' => '111101001', // Kas Utama -> KAS
            '1-1010' => '111101002', // Kas Kecil -> KAS Kecil
            '1-1100' => '111201002', // Bank Utama -> BANK Mandiri
            '1-1110' => '111201001', // Bank BCA -> BANK BCA
            '1-1200' => '111301001', // Piutang Dagang -> PIUTANG DAGANG
            '1-1300' => '111401001', // Persediaan Barang Dagang -> PERSEDIAAN BARANG DAGANG
            '2-1000' => '211101001', // Hutang Dagang -> HUTANG DAGANG
            '2-1200' => '211601004', // Penitipan Dana -> HUTANG PENITIPAN DANA PELANGGAN
            '3-1000' => '311101003', // Modal Pemilik -> MODAL PEMILIK
            '4-1000' => '411101001', // Pendapatan Penjualan -> PENJUALAN
            '5-1000' => '511501001', // Harga Pokok Penjualan (HPP) -> HARGA POKOK PENJUALAN
            '4-2000' => '411301001', // Potongan Voucher -> POTONGAN PENJUALAN
            '6-2000' => '611101026', // Beban Complimentary -> BIAYA COMPLIMENTARY
        ];

        foreach ($legacyDetailMap as $oldCode => $newCode) {
            $oldAcc = Account::where('code', $oldCode)->first();
            if ($oldAcc && !Account::where('code', $newCode)->exists()) {
                $oldAcc->update(['code' => $newCode]);
            }
        }

        // 3. Baca 97 akun resmi dari file CSV
        $csvPath = database_path('data/coa_diego_music_store_97_akun.csv');
        if (!File::exists($csvPath)) {
            // Fallback lokasi root proyek jika diakses lokal
            $csvPath = base_path('../../coa_diego_music_store_97_akun.csv');
        }

        if (!File::exists($csvPath)) {
            throw new \RuntimeException("File CSV COA tidak ditemukan di: {$csvPath}");
        }

        $rows = array_map('str_getcsv', file($csvPath));
        $headerRow = array_shift($rows); // buang header

        $subtypeHeadersMap = [
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

        // Store Level 2 header instances
        $level2Models = [];

        foreach ($rows as $row) {
            if (empty($row) || count($row) < 6) {
                continue;
            }

            $code = trim($row[1]);
            $name = trim($row[2]);
            $type = trim($row[3]);
            $subtype = trim($row[4]);
            $normalBalance = trim($row[5]);

            // 1. Tentukan Parent Header Root (Level 1)
            $firstDigit = substr($code, 0, 1);
            $rootCode = $firstDigit . '00000000';
            $rootId = $headerModels[$rootCode]->id ?? null;

            // 2. Buat Parent Header Subtype (Level 2) jika belum ada
            $level2Id = $rootId; // Fallback jika subtype tidak ditemukan
            if (isset($subtypeHeadersMap[$subtype])) {
                $subData = $subtypeHeadersMap[$subtype];
                if (!isset($level2Models[$subtype])) {
                    $level2Models[$subtype] = Account::updateOrCreate(
                        ['code' => $subData['code']],
                        [
                            'name' => $subData['name'],
                            'classification' => $type,
                            'account_subtype' => $subtype,
                            'normal_balance' => $normalBalance,
                            'is_header' => true,
                            'parent_id' => $rootId,
                            'is_active' => true,
                        ]
                    );
                }
                $level2Id = $level2Models[$subtype]->id;
            }

            // 3. Buat Akun Detail (Level 3) mengarah ke Level 2
            Account::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'classification' => $type,
                    'account_subtype' => $subtype,
                    'normal_balance' => $normalBalance,
                    'is_header' => false,
                    'parent_id' => $level2Id,
                    'is_active' => true,
                ]
            );
        }
    }
}
