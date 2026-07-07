<?php

namespace Tests\Unit\Helpers;

use App\Helpers\FormatHelper;
use PHPUnit\Framework\TestCase;

class FormatHelperTest extends TestCase
{
    /** @test */
    public function it_formats_currency_to_rupiah()
    {
        $this->assertEquals('Rp 0', FormatHelper::rupiah(0));
        $this->assertEquals('Rp 1.500', FormatHelper::rupiah(1500));
        $this->assertEquals('Rp 1.000.000', FormatHelper::rupiah(1000000));
    }

    /** @test */
    public function it_formats_positive_cash_change()
    {
        // 50,000 paid for 45,000 purchase -> Rp 5.000 change
        $this->assertEquals('Rp 5.000', FormatHelper::formatChange(50000, 45000));
        // 100,000 paid for 100,000 purchase -> Rp 0 change
        $this->assertEquals('Rp 0', FormatHelper::formatChange(100000, 100000));
    }

    /** @test */
    public function it_formats_negative_cash_change_as_deficiency()
    {
        // 40,000 paid for 50,000 purchase -> Kurang Rp 10.000
        $this->assertEquals('Kurang Rp 10.000', FormatHelper::formatChange(40000, 50000));
    }
}
