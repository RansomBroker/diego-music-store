<?php

namespace App\Helpers;

use App\Models\Account;

class AccountHelper
{
    /**
     * Mapping from legacy short account codes to new 9-digit COA codes.
     */
    public const LEGACY_CODE_MAP = [
        // Root Headers
        '1-0000' => '100000000', // ASET
        '2-0000' => '200000000', // LIABILITAS
        '3-0000' => '300000000', // EKUITAS
        '4-0000' => '400000000', // PENDAPATAN
        '5-0000' => '500000000', // HARGA POKOK PENJUALAN
        '6-0000' => '600000000', // BEBAN OPERASIONAL

        // Detail Accounts
        '1-1000' => '111101001', // KAS
        '1-1010' => '111101002', // KAS Kecil
        '1-1100' => '111201002', // BANK Mandiri (sebelumnya Bank Utama)
        '1-1110' => '111201001', // BANK BCA
        '1-1200' => '111301001', // PIUTANG DAGANG
        '1-1300' => '111401001', // PERSEDIAAN BARANG DAGANG
        '1-1400' => '111201006', // Uang Muka Pembelian
        '1-1500' => '111901001', // PPN DIBAYAR DIMUKA
        '2-1000' => '211101001', // HUTANG DAGANG
        '2-1100' => '211301001', // HUTANG PPH PASAL 21
        '2-1200' => '211601004', // HUTANG PENITIPAN DANA PELANGGAN
        '2-1500' => '211601002', // HUTANG ONGKIR
        '3-1000' => '311101003', // MODAL PEMILIK
        '4-1000' => '411101001', // PENJUALAN
        '4-1100' => '411201001', // RETUR PENJUALAN
        '4-2000' => '411301001', // POTONGAN PENJUALAN
        '5-1000' => '511501001', // HARGA POKOK PENJUALAN
        '6-1000' => '611101014', // BEBAN UMUM LAIN-LAIN
        '6-2000' => '611101026', // BIAYA COMPLIMENTARY
    ];

    /**
     * Find an account by new code or legacy code (bi-directional).
     */
    public static function findByCode(string $code): ?Account
    {
        // 1. Direct match
        $account = Account::where('code', $code)->first();
        if ($account) {
            return $account;
        }

        // 2. Legacy -> New code
        if (isset(self::LEGACY_CODE_MAP[$code])) {
            $account = Account::where('code', self::LEGACY_CODE_MAP[$code])->first();
            if ($account) {
                return $account;
            }
        }

        // 3. New -> Legacy code
        $reverseMap = array_flip(self::LEGACY_CODE_MAP);
        if (isset($reverseMap[$code])) {
            $account = Account::where('code', $reverseMap[$code])->first();
            if ($account) {
                return $account;
            }
        }

        return null;
    }

    /**
     * Resolve account ID by code (new or legacy), with optional creation fallback.
     */
    public static function resolveAccountId(string $code, string $defaultName = 'Default Account', string $classification = 'asset'): int
    {
        $targetCode = self::LEGACY_CODE_MAP[$code] ?? $code;
        $account = self::findByCode($targetCode);

        if (!$account) {
            $account = Account::firstOrCreate(
                ['code' => $targetCode],
                [
                    'name' => $defaultName,
                    'classification' => $classification,
                    'is_active' => true,
                ]
            );
        }

        return $account->id;
    }
}
