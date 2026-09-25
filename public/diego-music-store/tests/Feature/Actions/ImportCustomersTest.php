<?php

namespace Tests\Feature\Actions;

use App\Actions\Customer\ImportCustomers;
use App\Models\Customer;
use App\Models\CustomerLabel;
use App\Models\PricingTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportCustomersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        PricingTier::firstOrCreate(['name' => 'Umum / Retail']);
    }

    public function test_imports_customer_rows_successfully(): void
    {
        $action = app(ImportCustomers::class);

        $rows = [
            [
                'name' => 'John Petrucci',
                'phone' => '081299990001',
                'email' => 'john@dreamtheater.net',
                'address' => 'Jl. Gitars No. 7',
                'date_of_birth' => '1967-07-12',
                'customer_label' => 'VIP',
                'pricing_tier' => 'Umum / Retail',
                'is_loyalty_member' => 1,
                'loyalty_points' => 250,
                'outstanding_debt' => '1500000',
            ],
            [
                'name' => 'Steve Vai',
                'phone' => '081299990002',
                'email' => 'steve@vai.com',
                'address' => 'Jl. Jems No. 77',
                'date_of_birth' => '1960-06-06',
                'customer_label' => 'Studio',
                'pricing_tier' => 'Umum / Retail',
                'is_loyalty_member' => 0,
                'loyalty_points' => 0,
                'outstanding_debt' => '0',
            ],
        ];

        $result = $action->execute($rows);

        $this->assertEquals(2, $result['imported']);
        $this->assertEquals(0, $result['skipped']);
        $this->assertEmpty($result['errors']);

        $customer1 = Customer::where('phone', '081299990001')->first();
        $this->assertNotNull($customer1);
        $this->assertEquals('John Petrucci', $customer1->name);
        $this->assertEquals('VIP', $customer1->label?->name);
        $this->assertEquals(250, $customer1->loyalty_points);
        $this->assertTrue((bool)$customer1->is_loyalty_member);
        $this->assertEquals(1500000, (float)$customer1->outstanding_debt);

        $customer2 = Customer::where('phone', '081299990002')->first();
        $this->assertNotNull($customer2);
        $this->assertEquals('Steve Vai', $customer2->name);
        $this->assertEquals('Studio', $customer2->label?->name);
    }

    public function test_skips_row_with_empty_name(): void
    {
        $action = app(ImportCustomers::class);

        $rows = [
            [
                'name' => '',
                'phone' => '0811111111',
                '_row_number' => 2,
            ],
            [
                'name' => 'Valid Customer',
                'phone' => '0822222222',
                '_row_number' => 3,
            ]
        ];

        $result = $action->execute($rows);

        $this->assertEquals(1, $result['imported']);
        $this->assertEquals(1, $result['skipped']);
        $this->assertCount(1, $result['errors']);
        $this->assertStringContainsString("Baris 2: Kolom 'name' (Nama Pelanggan) wajib diisi.", $result['errors'][0]);
    }

    public function test_upserts_customer_when_phone_already_exists(): void
    {
        $action = app(ImportCustomers::class);

        $customer = Customer::create([
            'name' => 'Old Name',
            'phone' => '08555555555',
            'email' => 'old@email.com',
            'address' => 'Old Address',
        ]);

        $rows = [
            [
                'name' => 'Updated Name',
                'phone' => '08555555555',
                'email' => 'new@email.com',
                'address' => 'New Address',
                'customer_label' => 'VIP',
                'pricing_tier' => 'Umum / Retail',
                'is_loyalty_member' => 1,
                'loyalty_points' => 50,
                'outstanding_debt' => 0,
            ]
        ];

        $result = $action->execute($rows);

        $this->assertEquals(1, $result['imported']);
        $this->assertEquals(1, Customer::where('phone', '08555555555')->count());

        $customer->refresh();
        $this->assertEquals('Updated Name', $customer->name);
        $this->assertEquals('new@email.com', $customer->email);
        $this->assertEquals('New Address', $customer->address);
    }
}
