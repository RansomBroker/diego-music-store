<?php

namespace Tests\Feature\Actions\User;

use App\Actions\User\CreateUser;
use App\Actions\User\UpdateUser;
use App\Models\Branch;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Run the role and permission seeder
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_it_can_create_user_with_roles_and_branches(): void
    {
        $branch1 = Branch::create(['name' => 'Branch A', 'phone' => '123', 'address' => 'Addr A', 'is_active' => true]);
        $branch2 = Branch::create(['name' => 'Branch B', 'phone' => '456', 'address' => 'Addr B', 'is_active' => true]);
        
        $roleAdmin = Role::findByName('admin');
        $roleCashier = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);

        $data = [
            'name' => 'John Doe',
            'username' => 'johndoe',
            'email' => 'john@example.com',
            'password' => 'secret123',
            'branches' => [$branch1->id, $branch2->id],
            'roles' => [$roleAdmin->id, $roleCashier->id],
        ];

        /** @var CreateUser $action */
        $action = app(CreateUser::class);
        $user = $action->execute($data);

        // Assert user details saved
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('johndoe', $user->username);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertTrue(Hash::check('secret123', $user->password));

        // Assert branches synced
        $this->assertCount(2, $user->branches);
        $this->assertTrue($user->branches->contains($branch1));
        $this->assertTrue($user->branches->contains($branch2));

        // Assert roles synced
        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('cashier'));
    }

    public function test_it_can_update_user_and_sync_new_roles_and_branches(): void
    {
        $branch1 = Branch::create(['name' => 'Branch A', 'phone' => '123', 'address' => 'Addr A', 'is_active' => true]);
        $branch2 = Branch::create(['name' => 'Branch B', 'phone' => '456', 'address' => 'Addr B', 'is_active' => true]);

        $roleAdmin = Role::findByName('admin');
        $roleCashier = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);

        // Create initial user
        $user = User::create([
            'name' => 'Old Name',
            'username' => 'oldusername',
            'email' => 'old@example.com',
            'password' => 'oldpassword',
        ]);
        $user->branches()->sync([$branch1->id]);
        $user->assignRole($roleAdmin);

        $updateData = [
            'name' => 'New Name',
            'username' => 'newusername',
            'email' => 'new@example.com',
            'branches' => [$branch2->id], // Switch branch from A to B
            'roles' => [$roleCashier->id], // Switch role from admin to cashier
        ];

        /** @var UpdateUser $action */
        $action = app(UpdateUser::class);
        $updatedUser = $action->execute($user, $updateData);

        // Assert attributes updated
        $this->assertEquals('New Name', $updatedUser->name);
        $this->assertEquals('newusername', $updatedUser->username);
        $this->assertEquals('new@example.com', $updatedUser->email);

        // Assert old branches removed, new branches added
        $updatedUser->load('branches');
        $this->assertCount(1, $updatedUser->branches);
        $this->assertFalse($updatedUser->branches->contains($branch1));
        $this->assertTrue($updatedUser->branches->contains($branch2));

        // Assert old roles removed, new roles added
        $this->assertFalse($updatedUser->hasRole('admin'));
        $this->assertTrue($updatedUser->hasRole('cashier'));
    }

    public function test_it_does_not_override_password_on_update_if_not_provided(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'originalpassword',
        ]);

        $originalHash = $user->password;

        $updateData = [
            'name' => 'Test User Updated',
            'username' => 'testuser',
            'email' => 'test@example.com',
            // password key is missing, or null, or empty string
        ];

        /** @var UpdateUser $action */
        $action = app(UpdateUser::class);
        $updatedUser = $action->execute($user, $updateData);

        // Assert password has not changed
        $this->assertEquals($originalHash, $updatedUser->password);
    }

    public function test_it_can_create_and_update_user_with_avatar(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        /** @var CreateUser $createAction */
        $createAction = app(CreateUser::class);
        $user = $createAction->execute([
            'name' => 'Avatar User',
            'username' => 'avataruser',
            'email' => 'avatar@example.com',
            'password' => 'secret123',
            'avatar_url' => 'avatars/avatar1.jpg',
        ]);

        $this->assertEquals('avatars/avatar1.jpg', $user->avatar_url);

        \Illuminate\Support\Facades\Storage::disk('public')->put('avatars/avatar1.jpg', 'fake-image-content');
        $this->assertTrue(\Illuminate\Support\Facades\Storage::disk('public')->exists('avatars/avatar1.jpg'));

        /** @var UpdateUser $updateAction */
        $updateAction = app(UpdateUser::class);
        $updatedUser = $updateAction->execute($user, [
            'name' => 'Avatar User Updated',
            'username' => 'avataruser',
            'email' => 'avatar@example.com',
            'avatar_url' => 'avatars/avatar2.jpg',
        ]);

        $this->assertEquals('avatars/avatar2.jpg', $updatedUser->avatar_url);
        // Assert old avatar cleaned up
        $this->assertFalse(\Illuminate\Support\Facades\Storage::disk('public')->exists('avatars/avatar1.jpg'));
    }
}
