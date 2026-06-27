<?php

namespace App\Actions\Unit;

use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class CreateUnit
{
    /**
     * Execute the action to create a unit.
     *
     * @param  array<string, mixed>  $data
     * @return Unit
     */
    public function execute(array $data): Unit
    {
        return DB::transaction(function () use ($data) {
            return Unit::create($data);
        });
    }
}
