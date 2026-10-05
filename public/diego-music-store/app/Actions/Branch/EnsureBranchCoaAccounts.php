<?php

namespace App\Actions\Branch;

use App\Models\Account;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class EnsureBranchCoaAccounts
{
    /**
     * Ensure branch has all required COA accounts:
     * - inventory_account_id
     * - interbranch_receivable_account_id
     * - interbranch_payable_account_id
     */
    public static function execute(Branch $branch): Branch
    {
        return DB::transaction(function () use ($branch) {
            $branchIdPad = str_pad($branch->id, 3, '0', STR_PAD_LEFT);

            // Parent headers if available
            $invParent = Account::where('code', '111800000')->first(); // PERSEDIAAN
            $recParent = Account::where('code', '111600000')->first(); // PIUTANG USAHA
            $payParent = Account::where('code', '211100000')->orWhere('code', '211300000')->first(); // HUTANG

            // 1. Akun Persediaan
            if (!$branch->inventory_account_id) {
                $existingInv = Account::where('name', 'like', "%Persediaan Barang Dagang - {$branch->name}%")
                    ->orWhere('name', 'like', "%PERSEDIAAN BARANG DAGANG - " . strtoupper($branch->name) . "%")
                    ->first();

                if ($existingInv) {
                    $branch->inventory_account_id = $existingInv->id;
                } else {
                    $invCode = '11410' . $branchIdPad;
                    while (Account::where('code', $invCode)->where('id', '!=', $branch->inventory_account_id)->exists()) {
                        $invCode = (string)((int)$invCode + 1);
                    }
                    $invAccount = Account::firstOrCreate(
                        ['code' => $invCode],
                        [
                            'name' => 'Persediaan Barang Dagang - ' . $branch->name,
                            'classification' => 'asset',
                            'account_subtype' => 'inventory',
                            'normal_balance' => 'debit',
                            'is_active' => true,
                            'is_header' => false,
                            'parent_id' => $invParent?->id,
                        ]
                    );
                    $branch->inventory_account_id = $invAccount->id;
                }
            }

            // 2. Akun Piutang Antar Cabang
            if (!$branch->interbranch_receivable_account_id) {
                $existingRec = Account::where('name', 'like', "%Piutang Antar Cabang - {$branch->name}%")
                    ->orWhere('name', 'like', "%PIUTANG ANTAR CABANG - " . strtoupper($branch->name) . "%")
                    ->first();

                if ($existingRec) {
                    $branch->interbranch_receivable_account_id = $existingRec->id;
                } else {
                    $recCode = '14110' . $branchIdPad;
                    while (Account::where('code', $recCode)->where('id', '!=', $branch->interbranch_receivable_account_id)->exists()) {
                        $recCode = (string)((int)$recCode + 1);
                    }
                    $recAccount = Account::firstOrCreate(
                        ['code' => $recCode],
                        [
                            'name' => 'Piutang Antar Cabang - ' . $branch->name,
                            'classification' => 'asset',
                            'account_subtype' => 'receivable',
                            'normal_balance' => 'debit',
                            'is_active' => true,
                            'is_header' => false,
                            'parent_id' => $recParent?->id,
                        ]
                    );
                    $branch->interbranch_receivable_account_id = $recAccount->id;
                }
            }

            // 3. Akun Hutang Antar Cabang
            if (!$branch->interbranch_payable_account_id) {
                $existingPay = Account::where('name', 'like', "%Hutang Antar Cabang - {$branch->name}%")
                    ->orWhere('name', 'like', "%HUTANG ANTAR CABANG - " . strtoupper($branch->name) . "%")
                    ->first();

                if ($existingPay) {
                    $branch->interbranch_payable_account_id = $existingPay->id;
                } else {
                    $payCode = '21110' . $branchIdPad;
                    while (Account::where('code', $payCode)->where('id', '!=', $branch->interbranch_payable_account_id)->exists()) {
                        $payCode = (string)((int)$payCode + 1);
                    }
                    $payAccount = Account::firstOrCreate(
                        ['code' => $payCode],
                        [
                            'name' => 'Hutang Antar Cabang - ' . $branch->name,
                            'classification' => 'liability',
                            'account_subtype' => 'payable',
                            'normal_balance' => 'credit',
                            'is_active' => true,
                            'is_header' => false,
                            'parent_id' => $payParent?->id,
                        ]
                    );
                    $branch->interbranch_payable_account_id = $payAccount->id;
                }
            }

            $branch->save();

            return $branch;
        });
    }

    /**
     * Ensure all branches in the system have COA accounts provisioned.
     */
    public static function executeAll(): int
    {
        $count = 0;
        foreach (Branch::all() as $branch) {
            static::execute($branch);
            $count++;
        }
        return $count;
    }
}
