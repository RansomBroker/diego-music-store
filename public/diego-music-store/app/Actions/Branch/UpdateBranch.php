<?php

namespace App\Actions\Branch;

use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class UpdateBranch
{
    /**
     * Execute the action to update a branch.
     *
     * @param  Branch  $branch
     * @param  array<string, mixed>  $data
     * @return Branch
     */
    public function execute(Branch $branch, array $data): Branch
    {
        return DB::transaction(function () use ($branch, $data) {
            $branch->update($data);
            return $branch;
        });
    }
}
