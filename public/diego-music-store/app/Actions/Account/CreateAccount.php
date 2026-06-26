<?php

namespace App\Actions\Account;

use App\Models\Account;
use Illuminate\Support\Facades\DB;

class CreateAccount
{
    /**
     * Execute the action to create an account.
     *
     * @param  array<string, mixed>  $data
     * @return Account
     */
    public function execute(array $data): Account
    {
        return DB::transaction(function () use ($data) {
            return Account::create($data);
        });
    }
}
