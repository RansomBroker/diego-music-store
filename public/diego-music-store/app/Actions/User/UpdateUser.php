<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UpdateUser
{
    /**
     * Execute the action to update a user and sync branches and roles.
     *
     * @param  User  $user
     * @param  array<string, mixed>  $data
     * @return User
     */
    public function execute(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $hasBranches = array_key_exists('branches', $data);
            $hasRoles = array_key_exists('roles', $data);
            $branches = Arr::pull($data, 'branches', []);
            $roles = Arr::pull($data, 'roles', []);

            // Cleanup old avatar if replaced
            if (array_key_exists('avatar_url', $data) && $user->avatar_url && $user->avatar_url !== $data['avatar_url']) {
                Storage::disk('public')->delete($user->avatar_url);
            }

            // Update user details
            $user->update($data);

            // Sync relation data if provided
            if ($hasBranches) {
                $user->branches()->sync($branches);
            }
            if ($hasRoles) {
                $user->syncRoles($roles);
            }

            return $user;
        });
    }
}
