<?php

namespace App\Actions\Unit;

use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class UpdateUnit
{
    /**
     * Execute the action to update a unit.
     *
     * @param  Unit  $unit
     * @param  array<string, mixed>  $data
     * @return Unit
     */
    public function execute(Unit $unit, array $data): Unit
    {
        return DB::transaction(function () use ($unit, $data) {
            $unit->update($data);
            return $unit;
        });
    }
}
