<?php

namespace App\Actions\CustomerLabel;

use App\Models\CustomerLabel;
use Illuminate\Support\Facades\DB;

class UpdateCustomerLabel
{
    /**
     * Execute the action to update a customer label.
     *
     * @param  CustomerLabel  $customerLabel
     * @param  array<string, mixed>  $data
     * @return CustomerLabel
     */
    public function execute(CustomerLabel $customerLabel, array $data): CustomerLabel
    {
        return DB::transaction(function () use ($customerLabel, $data) {
            $customerLabel->update($data);
            return $customerLabel;
        });
    }
}
