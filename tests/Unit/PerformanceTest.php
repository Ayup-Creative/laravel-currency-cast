<?php

namespace AyupCreative\Casts\Currency\Tests\Unit;

use AyupCreative\Casts\Currency\Tests\UnitTestCase;
use AyupCreative\Casts\Currency\Values\Money;

class PerformanceTest extends UnitTestCase
{
    public function test_bulk_arithmetic_performance()
    {
        $start = microtime(true);
        $total = new Money(0, 'GBP');
        
        for ($i = 0; $i < 10000; $i++) {
            $total = $total->add(new Money($i, 'GBP'));
            $total = $total->subtract(new Money(1, 'GBP'));
            $total = $total->multiply(1.001);
            $total = $total->divide(1.001);
        }
        
        $end = microtime(true);
        $duration = $end - $start;
        
        // Ensure it completes in a reasonable time (e.g., under 1 second)
        $this->assertLessThan(1.0, $duration, "Bulk operations took too long: {$duration}s");
    }

    public function test_formatting_performance()
    {
        $start = microtime(true);
        $money = new Money(1234567, 'GBP');
        
        for ($i = 0; $i < 1000; $i++) {
            $money->formatted();
        }
        
        $end = microtime(true);
        $duration = $end - $start;
        
        // NumberFormatter is slower than simple math, but should still be reasonable
        $this->assertLessThan(1.0, $duration, "Bulk formatting took too long: {$duration}s");
    }
}
