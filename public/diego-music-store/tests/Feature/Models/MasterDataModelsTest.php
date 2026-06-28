<?php

namespace Tests\Feature\Models;

use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_customer(): void
    {
        $customer = Customer::create([
            'name' => 'John Doe',
            'phone' => '08123456789',
            'email' => 'john@example.com',
            'address' => 'Denpasar',
            'is_loyalty_member' => true,
            'loyalty_points' => 120,
        ]);

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertEquals('John Doe', $customer->name);
        $this->assertTrue($customer->is_loyalty_member);
        $this->assertEquals(120, $customer->loyalty_points);
    }

    public function test_it_can_create_supplier(): void
    {
        $supplier = Supplier::create([
            'name' => 'Yamaha Music Indonesia',
            'contact_person' => 'Budi',
            'phone' => '08123456780',
            'email' => 'yamaha@example.com',
            'address' => 'Jakarta',
            'bank_name' => 'BCA',
            'bank_account_number' => '123456789',
            'bank_account_name' => 'PT Yamaha Music',
            'outstanding_debt' => 5000000.00,
        ]);

        $this->assertInstanceOf(Supplier::class, $supplier);
        $this->assertEquals('Yamaha Music Indonesia', $supplier->name);
        $this->assertEquals(5000000.00, $supplier->outstanding_debt);
    }

    public function test_it_can_create_account(): void
    {
        $account = Account::create([
            'code' => '1-1000',
            'name' => 'Kas Utama',
            'classification' => 'asset',
            'is_active' => true,
        ]);

        $this->assertInstanceOf(Account::class, $account);
        $this->assertEquals('1-1000', $account->code);
        $this->assertEquals('Kas Utama', $account->name);
        $this->assertEquals('asset', $account->classification);
        $this->assertTrue($account->is_active);
    }
}
