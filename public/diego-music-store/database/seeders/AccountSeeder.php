<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
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
            '2-1200' => '211601004', // Penitipan Dana -> HUTANG PENITIPAN DANA
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
        $headerRow = array_shift($rows); // buang header: no,kode_akun,nama_akun,account_type,account_subtype,normal_balance

        foreach ($rows as $row) {
            if (empty($row) || count($row) < 6) {
                continue;
            }

            $code = trim($row[1]);
            $name = trim($row[2]);
            $type = trim($row[3]);
            $subtype = trim($row[4]);
            $normalBalance = trim($row[5]);

            // Tentukan Parent Header berdasarkan digit pertama
            $firstDigit = substr($code, 0, 1);
            $parentCode = $firstDigit . '00000000';
            $parentId = $headerModels[$parentCode]->id ?? null;

            Account::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'classification' => $type,
                    'account_subtype' => $subtype,
                    'normal_balance' => $normalBalance,
                    'is_header' => false,
                    'parent_id' => $parentId,
                    'is_active' => true,
                ]
            );
        }
    }
}
