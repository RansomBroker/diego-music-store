<?php

namespace App\Actions\Customer;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class UpdateCustomer
{
    /**
     * Execute the action to update a customer.
     *
     * @param  Customer  $customer
     * @param  array<string, mixed>  $data
     * @return Customer
     */
    public function execute(Customer $customer, array $data): Customer
    {
        return DB::transaction(function () use ($customer, $data) {
            $customer->update($data);
            return $customer;
        });
    }
}
