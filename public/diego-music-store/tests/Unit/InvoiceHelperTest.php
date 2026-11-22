<?php

namespace Tests\Unit;

use App\Helpers\InvoiceHelper;
use PHPUnit\Framework\TestCase;

class InvoiceHelperTest extends TestCase
{
    public function test_preserves_prefix_case_and_avoids_double_dash_with_trailing_hyphen(): void
    {
        $this->assertSame('INV-O-A0001', InvoiceHelper::getInvoiceNumber('INV-O', 'A', 'Z', 4, 1));
        $this->assertSame('INV-O-A0001', InvoiceHelper::getInvoiceNumber('INV-O-', 'A', 'Z', 4, 1));
        $this->assertSame('inv-o-A0001', InvoiceHelper::getInvoiceNumber('inv-o', 'A', 'Z', 4, 1));
        $this->assertSame('inv-o-A0001', InvoiceHelper::getInvoiceNumber('inv-o-', 'A', 'Z', 4, 1));
        $this->assertSame('inv-o-A0001', InvoiceHelper::getInvoiceNumber(' inv-o-- ', 'A', 'Z', 4, 1));
        $this->assertSame('MixCase-A0002', InvoiceHelper::getInvoiceNumber('MixCase-', 'A', 'Z', 4, 2));
        $this->assertSame('A0001', InvoiceHelper::getInvoiceNumber('-', 'A', 'Z', 4, 1));
        $this->assertSame('A0001', InvoiceHelper::getInvoiceNumber(null, 'A', 'Z', 4, 1));
    }
}
