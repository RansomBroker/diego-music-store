<?php

namespace App\Actions\CustomerLabel;

use App\Models\CustomerLabel;
use Illuminate\Support\Facades\DB;

class CreateCustomerLabel
{
    /**
     * Execute the action to create a customer label.
     *
     * @param  array<string, mixed>  $data
     * @return CustomerLabel
     */
    public function execute(array $data): CustomerLabel
    {
        return DB::transaction(function () use ($data) {
            return CustomerLabel::create($data);
        });
    }
}
