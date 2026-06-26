<?php

namespace App\Actions\Supplier;

use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class CreateSupplier
{
    /**
     * Execute the action to create a supplier.
     *
     * @param  array<string, mixed>  $data
     * @return Supplier
     */
    public function execute(array $data): Supplier
    {
        return DB::transaction(function () use ($data) {
            return Supplier::create($data);
        });
    }
}
