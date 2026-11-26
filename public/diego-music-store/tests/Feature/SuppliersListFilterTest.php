<?php

namespace Tests\Feature;

use App\Filament\Resources\Suppliers\Pages\ListSuppliers;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SuppliersListFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_suppliers_defaults_to_all_and_filters_by_supplier_and_vendor(): void
    {
        foreach (['bo.access', 'bo.suppliers.view'] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(['name' => 'supplier_viewer_test', 'guard_name' => 'web']);
        $role->syncPermissions(['bo.access', 'bo.suppliers.view']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $this->actingAs($user);

        $supplier = Supplier::create([
            'name' => 'PT Musik Supplier',
            'status' => Supplier::STATUS_SUPPLIER,
        ]);

        $vendor = Supplier::create([
            'name' => 'CV Jasa Vendor',
            'status' => Supplier::STATUS_VENDOR,
        ]);

        // Default shows all (both Supplier and Vendor)
        Livewire::test(ListSuppliers::class)
            ->assertCanSeeTableRecords([$supplier, $vendor])
            ->set('activeTab', 'supplier')
            ->assertCanSeeTableRecords([$supplier])
            ->assertCanNotSeeTableRecords([$vendor])
            ->set('activeTab', 'vendor')
            ->assertCanSeeTableRecords([$vendor])
            ->assertCanNotSeeTableRecords([$supplier])
            ->set('activeTab', 'all')
            ->filterTable('status', Supplier::STATUS_VENDOR)
            ->assertCanSeeTableRecords([$vendor])
            ->assertCanNotSeeTableRecords([$supplier]);
    }
}

