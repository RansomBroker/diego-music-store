<?php

namespace App\Actions\Account;

use App\Models\Account;
use Illuminate\Support\Facades\DB;

class UpdateAccount
{
    /**
     * Execute the action to update an account.
     *
     * @param  Account  $account
     * @param  array<string, mixed>  $data
     * @return Account
     */
    public function execute(Account $account, array $data): Account
    {
        return DB::transaction(function () use ($account, $data) {
            $account->update($data);
            return $account;
        });
    }
}
