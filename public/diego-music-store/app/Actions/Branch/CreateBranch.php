<?php

namespace App\Actions\Branch;

use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class CreateBranch
{
    /**
     * Execute the action to create a branch.
     *
     * @param  array<string, mixed>  $data
     * @return Branch
     */
    public function execute(array $data): Branch
    {
        return DB::transaction(function () use ($data) {
            return Branch::create($data);
        });
    }
}
