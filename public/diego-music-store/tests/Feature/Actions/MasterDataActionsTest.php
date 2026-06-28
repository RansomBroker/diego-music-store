<?php

namespace Tests\Feature\Actions;

use App\Actions\Customer\CreateCustomer;
use App\Actions\Customer\UpdateCustomer;
use App\Actions\Supplier\CreateSupplier;
use App\Actions\Supplier\UpdateSupplier;
use App\Actions\Account\CreateAccount;
use App\Actions\Account\UpdateAccount;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_actions(): void
    {
        $createData = [
            'name' => 'Alice',
            'phone' => '08777777777',
            'email' => 'alice@example.com',
            'address' => 'Denpasar',
            'is_loyalty_member' => true,
            'loyalty_points' => 50,
        ];

        $customer = app(CreateCustomer::class)->execute($createData);

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertEquals('Alice', $customer->name);
        $this->assertEquals(50, $customer->loyalty_points);

        $updateData = [
            'name' => 'Alice In Wonderland',
            'phone' => '08777777777',
            'email' => 'alice.new@example.com',
            'address' => 'Kuta',
            'is_loyalty_member' => false,
            'loyalty_points' => 0,
        ];

        $updated = app(UpdateCustomer::class)->execute($customer, $updateData);

        $this->assertEquals('Alice In Wonderland', $updated->name);
        $this->assertEquals('alice.new@example.com', $updated->email);
        $this->assertFalse($updated->is_loyalty_member);
        $this->assertEquals(0, $updated->loyalty_points);
    }

    public function test_supplier_actions(): void
    {
        $createData = [
            'name' => 'Supplier A',
            'contact_person' => 'Charlie',
            'phone' => '0812341234',
            'email' => 'supplier@example.com',
            'address' => 'Jakarta',
            'bank_name' => 'Mandiri',
            'bank_account_number' => '987654321',
            'bank_account_name' => 'PT Supplier',
            'outstanding_debt' => 0,
        ];

        $supplier = app(CreateSupplier::class)->execute($createData);

        $this->assertInstanceOf(Supplier::class, $supplier);
        $this->assertEquals('Supplier A', $supplier->name);

        $updateData = [
            'name' => 'Supplier A Premium',
            'outstanding_debt' => 1000000,
        ];

        $updated = app(UpdateSupplier::class)->execute($supplier, $updateData);

        $this->assertEquals('Supplier A Premium', $updated->name);
        $this->assertEquals(1000000, $updated->outstanding_debt);
    }

    public function test_account_actions(): void
    {
        $createData = [
            'code' => '1-1000',
            'name' => 'Cash',
            'classification' => 'asset',
            'is_active' => true,
        ];

        $account = app(CreateAccount::class)->execute($createData);

        $this->assertInstanceOf(Account::class, $account);
        $this->assertEquals('1-1000', $account->code);

        $updateData = [
            'name' => 'Main Cash',
            'is_active' => false,
        ];

        $updated = app(UpdateAccount::class)->execute($account, $updateData);

        $this->assertEquals('Main Cash', $updated->name);
        $this->assertFalse($updated->is_active);
    }
}
