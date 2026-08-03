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

    /** @test */
    public function it_parses_formatted_rupiah_string_to_float()
    {
        $this->assertEquals(1000000.0, FormatHelper::parseRupiah('1.000.000'));
        $this->assertEquals(1000000.0, FormatHelper::parseRupiah('Rp 1.000.000'));
        $this->assertEquals(250000.0, FormatHelper::parseRupiah('250.000'));
        $this->assertEquals(0.0, FormatHelper::parseRupiah(''));
        $this->assertEquals(0.0, FormatHelper::parseRupiah(null));
        $this->assertEquals(5000.0, FormatHelper::parseRupiah(5000));
    }

    /** @test */
    public function it_formats_input_number_with_thousands_separator()
    {
        $this->assertEquals('1.000.000', FormatHelper::formatInputNumber(1000000));
        $this->assertEquals('250.000', FormatHelper::formatInputNumber('250000'));
        $this->assertEquals('0', FormatHelper::formatInputNumber(0));
        $this->assertEquals('0', FormatHelper::formatInputNumber(''));
    }
}
