<?php

namespace App\Actions\Employee;

use App\Helpers\BranchHelper;
use App\Helpers\PermissionHelper;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ImportEmployees
{
    /**
     * Canonical headers expected for employee + user account import.
     *
     * @var array<int, string>
     */
    public const REQUIRED_HEADERS = [
        'nama',
        'user',
        'role',
        'password',
    ];

    /**
     * Import employees along with their login user accounts, branches, and roles.
     * Existing roles (case-insensitive) are reused; missing roles are created automatically.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{imported: int, skipped: int, errors: array<int, string>, created_roles: array<int, string>}
     */
    public function execute(array $rows, ?int $branchId = null): array
    {
        PermissionHelper::enforceActionPermission(Auth::user(), 'bo.employees.create');

        $targetBranchId = $branchId
            ?? (Auth::check() ? BranchHelper::getActiveBranchId() : Branch::query()->where('is_active', true)->value('id'));

        $importedCount = 0;
        $skippedCount = 0;
        $errors = [];
        $createdRoles = [];
        $hasNewRole = false;

        DB::transaction(function () use (
            $rows,
            $targetBranchId,
            &$importedCount,
            &$skippedCount,
            &$errors,
            &$createdRoles,
            &$hasNewRole
        ): void {
            foreach ($rows as $index => $row) {
                $rowNum = $row['_row_number'] ?? ($index + 2);

                $name = trim((string) ($row['nama'] ?? $row['name'] ?? ''));
                $username = Str::lower(trim((string) ($row['user'] ?? $row['username'] ?? '')));
                $roleName = trim((string) ($row['role'] ?? ''));
                $password = trim((string) ($row['password'] ?? ''));

                if ($name === '' && $username === '' && $roleName === '' && $password === '') {
                    continue;
                }

                if ($name === '') {
                    $skippedCount++;
                    $errors[] = "Baris {$rowNum}: Kolom 'nama' wajib diisi.";
                    continue;
                }

                if ($username === '') {
                    $skippedCount++;
                    $errors[] = "Baris {$rowNum}: Kolom 'user' (username) wajib diisi.";
                    continue;
                }

                if ($roleName === '') {
                    $skippedCount++;
                    $errors[] = "Baris {$rowNum}: Kolom 'role' wajib diisi.";
                    continue;
                }

                if (PermissionHelper::isProtectedRoleName($roleName) && Auth::check() && ! PermissionHelper::isOwner(Auth::user())) {
                    $skippedCount++;
                    $errors[] = "Baris {$rowNum}: Role terlindungi '{$roleName}' hanya dapat ditetapkan oleh Owner.";
                    continue;
                }

                [$role, $wasCreated] = $this->resolveOrCreateRole($roleName);
                if ($wasCreated) {
                    $hasNewRole = true;
                    $createdRoles[] = $role->name;
                }

                $existingUser = User::query()
                    ->whereRaw('LOWER(username) = ?', [$username])
                    ->first();

                if (! $existingUser && $password === '') {
                    $skippedCount++;
                    $errors[] = "Baris {$rowNum}: Kolom 'password' wajib diisi untuk user baru '{$username}'.";
                    continue;
                }

                $rawEmail = isset($row['email']) ? trim((string) $row['email']) : '';
                $email = $this->resolveUniqueEmail($username, $rawEmail, $existingUser?->id);

                if ($existingUser) {
                    $updatePayload = [
                        'name' => $name,
                        'is_active' => true,
                    ];
                    if ($password !== '') {
                        $updatePayload['password'] = $password;
                    }
                    if ($rawEmail !== '' && $email !== $existingUser->email) {
                        $updatePayload['email'] = $email;
                    }
                    $existingUser->update($updatePayload);
                    $user = $existingUser;
                } else {
                    $user = User::create([
                        'name' => $name,
                        'username' => $username,
                        'email' => $email,
                        'password' => $password,
                        'is_active' => true,
                    ]);
                }

                $user->syncRoles([$role]);

                if ($targetBranchId) {
                    $user->branches()->syncWithoutDetaching([$targetBranchId]);
                }

                $this->syncEmployeeRecord($user, $name, $targetBranchId, $row, $rawEmail !== '' ? $email : null);

                $importedCount++;
            }
        });

        if ($hasNewRole) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        return [
            'imported' => $importedCount,
            'skipped' => $skippedCount,
            'errors' => $errors,
            'created_roles' => array_values(array_unique($createdRoles)),
        ];
    }

    /**
     * Find an existing role case-insensitively or create it on the web guard.
     *
     * @return array{0: Role, 1: bool}
     */
    private function resolveOrCreateRole(string $roleName): array
    {
        $existing = Role::query()
            ->where('guard_name', 'web')
            ->whereRaw('LOWER(name) = ?', [Str::lower($roleName)])
            ->first();

        if ($existing) {
            return [$existing, false];
        }

        $created = Role::create([
            'name' => $roleName,
            'guard_name' => 'web',
        ]);

        return [$created, true];
    }

    /**
     * Ensure every user row receives a unique non-empty email address.
     */
    private function resolveUniqueEmail(string $username, string $rawEmail, ?int $ignoreUserId = null): string
    {
        if ($rawEmail !== '') {
            $exists = User::query()
                ->where('email', $rawEmail)
                ->when($ignoreUserId, fn ($q) => $q->where('id', '!=', $ignoreUserId))
                ->exists();

            if (! $exists) {
                return $rawEmail;
            }
        }

        $localPart = preg_replace('/[^a-z0-9._-]+/', '', $username) ?: 'karyawan';
        $base = "{$localPart}@diegomusic.local";
        $candidate = $base;
        $counter = 1;

        while (
            User::query()
                ->where('email', $candidate)
                ->when($ignoreUserId, fn ($q) => $q->where('id', '!=', $ignoreUserId))
                ->exists()
        ) {
            $candidate = "{$localPart}-{$counter}@diegomusic.local";
            $counter++;
        }

        return $candidate;
    }

    /**
     * Create or update the linked Employee row for the imported user.
     *
     * @param  array<string, mixed>  $row
     */
    private function syncEmployeeRecord(
        User $user,
        string $name,
        ?int $branchId,
        array $row,
        ?string $providedEmail
    ): Employee {
        $employee = Employee::withTrashed()
            ->where('user_id', $user->id)
            ->first();

        if (! $employee && $branchId) {
            $employee = Employee::withTrashed()
                ->whereNull('user_id')
                ->where('branch_id', $branchId)
                ->whereRaw('LOWER(name) = ?', [Str::lower($name)])
                ->first();
        }

        $phone = isset($row['phone']) && trim((string) $row['phone']) !== '' ? trim((string) $row['phone']) : null;
        $address = isset($row['address']) && trim((string) $row['address']) !== '' ? trim((string) $row['address']) : null;
        $nik = isset($row['nik']) && trim((string) $row['nik']) !== '' ? trim((string) $row['nik']) : null;

        if ($employee) {
            if ($employee->trashed()) {
                $employee->restore();
            }

            $employee->update([
                'user_id' => $user->id,
                'branch_id' => $branchId ?? $employee->branch_id,
                'name' => $name,
                'phone' => $phone ?? $employee->phone,
                'email' => $providedEmail ?? $employee->email,
                'address' => $address ?? $employee->address,
                'is_active' => true,
            ]);

            return $employee;
        }

        return Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branchId,
            'nik' => $nik ?: $this->generateNextNik(),
            'name' => $name,
            'phone' => $phone,
            'email' => $providedEmail,
            'address' => $address,
            'is_active' => true,
        ]);
    }

    private function generateNextNik(): string
    {
        $nextNumber = ((int) Employee::withTrashed()->max('id')) + 1;
        $candidate = 'EMP-' . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);

        while (Employee::withTrashed()->where('nik', $candidate)->exists()) {
            $nextNumber++;
            $candidate = 'EMP-' . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
        }

        return $candidate;
    }
}
