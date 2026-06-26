<?php

namespace App\Actions\Customer;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class CreateCustomer
{
    /**
     * Execute the action to create a customer.
     *
     * @param  array<string, mixed>  $data
     * @return Customer
     */
    public function execute(array $data): Customer
    {
        return DB::transaction(function () use ($data) {
            return Customer::create($data);
        });
    }
}
