<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreateUser
{
    /**
     * Execute the action to create a user and assign branches and roles.
     *
     * @param  array<string, mixed>  $data
     * @return User
     */
    public function execute(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // Extract relation arrays
            $branches = Arr::pull($data, 'branches', []);
            $roles = Arr::pull($data, 'roles', []);

            // Create the user record
            $user = User::create($data);

            // Sync relation data
            $user->branches()->sync($branches);
            $user->syncRoles($roles);

            return $user;
        });
    }
}
