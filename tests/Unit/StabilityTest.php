<?php

namespace AyupCreative\Casts\Currency\Tests\Unit;

use AyupCreative\Casts\Currency\Tests\UnitTestCase;
use AyupCreative\Casts\Currency\Values\Money;

class StabilityTest extends UnitTestCase
{
    public function test_it_handles_large_integers()
    {
        // 1 trillion cents
        $amount = 1000000000000;
        $money = new Money($amount, 'GBP');
        $this->assertEquals($amount, $money->raw());
        $this->assertEquals(10000000000.0, $money->value());
        
        // Multiplications with large values
        $result = $money->multiply(1.5);
        $this->assertEquals(1500000000000, $result->raw());
    }

    public function test_it_handles_negative_values_and_multipliers()
    {
        $money = new Money(-1000, 'GBP');
        $this->assertTrue($money->isNegative());
        
        $result = $money->multiply(-2);
        $this->assertEquals(2000, $result->raw());
        $this->assertTrue($result->isPositive());
    }

    public function test_float_precision_stability()
    {
        // 0.1 + 0.2 is a classic float problem
        $m1 = Money::fromFloat(0.1);
        $m2 = Money::fromFloat(0.2);
        $sum = $m1->add($m2);
        
        $this->assertEquals(30, $sum->raw());
        $this->assertEquals(0.3, $sum->value());

        // More complex precision
        $m3 = Money::fromFloat(1234.567);
        $this->assertEquals(123457, $m3->raw()); // Round HALF_UP from 123456.7
    }

    public function test_extreme_rounding_modes()
    {
        $m1 = Money::fromFloat(1.234, 'GBP', PHP_ROUND_HALF_UP);
        $this->assertEquals(123, $m1->raw());

        $m2 = Money::fromFloat(1.235, 'GBP', PHP_ROUND_HALF_UP);
        $this->assertEquals(124, $m2->raw());

        $m3 = Money::fromFloat(1.235, 'GBP', PHP_ROUND_HALF_DOWN);
        $this->assertEquals(123, $m3->raw());
    }
}
