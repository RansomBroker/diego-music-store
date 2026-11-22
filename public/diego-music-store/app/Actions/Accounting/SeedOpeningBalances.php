<?php

namespace App\Actions\Accounting;

use App\Helpers\AccountHelper;
use App\Models\Account;
use App\Models\Branch;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use Illuminate\Support\Facades\DB;

class SeedOpeningBalances
{
    /**
     * Saldo awal neraca per kode akun COA (Cabang Pusat).
     * Akun kontra aset & ekuitas laba berada pada sisi kredit agar neraca sesuai standar akuntansi.
     *
     * @var array<int, array{code: string, debit: int, credit: int, notes: string}>
     */
    public const OPENING_LINES = [
        // Aktiva Lancar
        ['code' => '111101001', 'debit' => 18911200, 'credit' => 0, 'notes' => 'Saldo Awal Kas'],
        ['code' => '111101002', 'debit' => 349100, 'credit' => 0, 'notes' => 'Saldo Awal Kas Kecil'],
        ['code' => '111201001', 'debit' => 58286795, 'credit' => 0, 'notes' => 'Saldo Awal Bank BCA'],
        ['code' => '111201002', 'debit' => 36094611, 'credit' => 0, 'notes' => 'Saldo Awal Bank Mandiri'],
        ['code' => '111201003', 'debit' => 20350993, 'credit' => 0, 'notes' => 'Saldo Awal Bank BRI'],
        ['code' => '111201004', 'debit' => 8112109, 'credit' => 0, 'notes' => 'Saldo Awal Bank BNI'],
        ['code' => '111201005', 'debit' => 100339014, 'credit' => 0, 'notes' => 'Saldo Awal Bank Danamon'],
        ['code' => '111202006', 'debit' => 13861897, 'credit' => 0, 'notes' => 'Saldo Awal Tokopedia'],
        ['code' => '111202007', 'debit' => 16843950, 'credit' => 0, 'notes' => 'Saldo Awal Shopee'],
        ['code' => '111301001', 'debit' => 186754964, 'credit' => 0, 'notes' => 'Saldo Awal Piutang Dagang'],
        ['code' => '111701001', 'debit' => 113333327, 'credit' => 0, 'notes' => 'Saldo Awal Sewa Dibayar Dimuka'],

        // Aktiva Tetap & Aset Tidak Berwujud + Akumulasi Penyusutan (Kredit)
        ['code' => '121101001', 'debit' => 100208326, 'credit' => 0, 'notes' => 'Saldo Awal Kendaraan'],
        ['code' => '121201001', 'debit' => 0, 'credit' => 31597230, 'notes' => 'Saldo Awal Akumulasi Penyusutan Kendaraan'],
        ['code' => '121501001', 'debit' => 134179971, 'credit' => 0, 'notes' => 'Saldo Awal Inventaris Kantor'],
        ['code' => '121601001', 'debit' => 0, 'credit' => 83514876, 'notes' => 'Saldo Awal Akumulasi Penyusutan Inventaris Kantor'],
        ['code' => '128801001', 'debit' => 29000000, 'credit' => 0, 'notes' => 'Saldo Awal Software'],
        ['code' => '128801002', 'debit' => 0, 'credit' => 9883347, 'notes' => 'Saldo Awal Akumulasi Penyusutan Aset Tidak Berwujud'],

        // Liabilitas (kecuali Hutang Dagang 211101001 — diinput lewat Import Saldo Awal Hutang Supplier) & Ekuitas Laba
        ['code' => '211601002', 'debit' => 0, 'credit' => 1095600, 'notes' => 'Saldo Awal Hutang Ongkir'],
        ['code' => '311201001', 'debit' => 0, 'credit' => 420971996, 'notes' => 'Saldo Awal Laba Ditahan'],
        ['code' => '311301001', 'debit' => 0, 'credit' => 378550240, 'notes' => 'Saldo Awal Laba Tahun Berjalan'],
    ];

    /**
     * Seed opening balances once when Cabang Pusat has not been operated on
     * and target accounts are still zero.
     */
    public function execute(): ?JournalEntry
    {
        $activeBranches = Branch::query()->where('is_active', true)->orderBy('id')->get();
        if ($activeBranches->isEmpty()) {
            return null;
        }

        $branch = $activeBranches->first(fn (Branch $b) => str_contains(mb_strtolower($b->name), 'pusat'))
            ?? $activeBranches->first();

        if (! $branch) {
            return null;
        }

        // Guard 1: Sudah pernah dibukukan sebelumnya pada cabang ini
        $alreadySeeded = JournalEntry::query()
            ->where('branch_id', $branch->id)
            ->where('reference_type', 'OpeningBalance')
            ->exists();

        if ($alreadySeeded) {
            return null;
        }

        // Guard 2: Sudah ada jurnal operasional berjalan pada cabang ini (selain import awal stok/hutang)
        $hasOperationalJournals = JournalEntry::query()
            ->where('branch_id', $branch->id)
            ->where(function ($query): void {
                $query->whereNotIn('reference_type', ['InitialStock', 'InitialDebt'])
                    ->orWhereNull('reference_type');
            })
            ->exists();

        if ($hasOperationalJournals) {
            return null;
        }

        return $this->seedForBranch($branch);
    }

    /**
     * Build and post the balanced OpeningBalance journal for accounts whose mutations are still zero.
     */
    private function seedForBranch(Branch $branch): ?JournalEntry
    {
        $resolvedAccounts = [];
        foreach (self::OPENING_LINES as $line) {
            $account = AccountHelper::findByCode($line['code'], $branch->id);
            if ($account) {
                $resolvedAccounts[$line['code']] = $account;
            }
        }

        if ($resolvedAccounts === []) {
            return null;
        }

        $accountIds = array_values(array_unique(array_map(fn (Account $a) => $a->id, $resolvedAccounts)));
        $existingSumsByAccount = DB::table('journal_items')
            ->whereIn('account_id', $accountIds)
            ->select(
                'account_id',
                DB::raw('SUM(debit) as total_debit'),
                DB::raw('SUM(credit) as total_credit')
            )
            ->groupBy('account_id')
            ->get()
            ->keyBy('account_id');

        $preparedItems = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach (self::OPENING_LINES as $line) {
            $account = $resolvedAccounts[$line['code']] ?? null;
            if (! $account) {
                continue;
            }

            // Guard 3: Jika saldo/mutasi akun sudah tidak 0 (misal dari import hutang awal), lewati akun ini
            $existing = $existingSumsByAccount->get($account->id);
            $hasNonZeroActivity = $existing
                && (((float) $existing->total_debit) != 0.0 || ((float) $existing->total_credit) != 0.0);

            if ($hasNonZeroActivity) {
                continue;
            }

            $preparedItems[] = [
                'account_id' => $account->id,
                'debit' => $line['debit'],
                'credit' => $line['credit'],
                'notes' => $line['notes'],
            ];

            $totalDebit += $line['debit'];
            $totalCredit += $line['credit'];
        }

        if ($preparedItems === []) {
            return null;
        }

        // Penyeimbang otomatis ke Modal Pemilik (311101003) agar double-entry tetap seimbang 100%
        $diff = $totalDebit - $totalCredit;
        if ($diff !== 0) {
            $contraAccount = AccountHelper::findByCode('311101003', $branch->id)
                ?? AccountHelper::findByCode('311101001', $branch->id);

            if (! $contraAccount) {
                return null;
            }

            $preparedItems[] = [
                'account_id' => $contraAccount->id,
                'debit' => $diff < 0 ? abs($diff) : 0,
                'credit' => $diff > 0 ? $diff : 0,
                'notes' => 'Penyeimbang Saldo Awal Neraca (Modal Pemilik)',
            ];
        }

        return DB::transaction(function () use ($branch, $preparedItems): JournalEntry {
            $entry = JournalEntry::create([
                'branch_id' => $branch->id,
                'entry_no' => JournalEntry::generateEntryNo($branch->id),
                'date' => now()->format('Y-m-d'),
                'description' => 'Saldo Awal Neraca (Bypass COA Awal)',
                'reference_type' => 'OpeningBalance',
                'status' => 'posted',
                'posted_at' => now(),
            ]);

            foreach ($preparedItems as $item) {
                JournalItem::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $item['account_id'],
                    'debit' => $item['debit'],
                    'credit' => $item['credit'],
                    'notes' => $item['notes'],
                ]);
            }

            return $entry;
        });
    }
}

