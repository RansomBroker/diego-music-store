<?php

namespace App\Actions\Supplier;

use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class UpdateSupplier
{
    /**
     * Execute the action to update a supplier.
     *
     * @param  Supplier  $supplier
     * @param  array<string, mixed>  $data
     * @return Supplier
     */
    public function execute(Supplier $supplier, array $data): Supplier
    {
        return DB::transaction(function () use ($supplier, $data) {
            $supplier->update($data);
            return $supplier;
        });
    }
}
