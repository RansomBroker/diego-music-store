<?php

namespace Tests\Unit\Helpers;

use App\Helpers\FinancialReportHelper;
use Tests\TestCase;

class FinancialReportHelperTest extends TestCase
{
    public function test_formats_negative_rupiah_and_numbers_with_parentheses(): void
    {
        $this->assertSame('(Rp 50.000)', FinancialReportHelper::formatRupiah(-50000));
        $this->assertSame('Rp 0', FinancialReportHelper::formatRupiah(0));
        $this->assertSame('Rp 1.250.000', FinancialReportHelper::formatRupiah(1250000));

        $this->assertSame('(50.000)', FinancialReportHelper::formatNumber(-50000));
        $this->assertSame('-', FinancialReportHelper::formatNumber(0));
        $this->assertSame('0', FinancialReportHelper::formatNumber(0, false));
        $this->assertSame('50.000', FinancialReportHelper::formatNumber(50000));
    }
}

