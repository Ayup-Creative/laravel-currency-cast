<?php

namespace AyupCreative\Casts\Currency\Tests\Unit;

use AyupCreative\Casts\Currency\Tests\UnitTestCase;
use AyupCreative\Casts\Currency\Values\Money;
use InvalidArgumentException;

class MoneyTest extends UnitTestCase
{
    public function test_it_can_be_instantiated()
    {
        $money = new Money(100, 'GBP');
        $this->assertEquals(100, $money->raw());
        $this->assertEquals(1.0, $money->value());
        $this->assertEquals('GBP', $money->currency());
    }

    public function test_from_float_rounds_correctly()
    {
        // 1.23 should be 123
        $money = Money::fromFloat(1.23);
        $this->assertEquals(123, $money->raw());

        // FIX VERIFIED: 1.235 should be 124 (with HALF_UP)
        $money = Money::fromFloat(1.235);
        $this->assertEquals(124, $money->raw());
    }

    public function test_it_can_add_money()
    {
        $m1 = new Money(100, 'GBP');
        $m2 = new Money(50, 'GBP');
        $m3 = $m1->add($m2);

        $this->assertEquals(150, $m3->raw());
        $this->assertEquals('GBP', $m3->currency());
    }

    public function test_it_cannot_add_different_currencies()
    {
        $this->expectException(InvalidArgumentException::class);

        $m1 = new Money(100, 'GBP');
        $m2 = new Money(50, 'USD');
        $m1->add($m2);
    }

    public function test_it_can_subtract_money()
    {
        $m1 = new Money(100, 'GBP');
        $m2 = new Money(50, 'GBP');
        $m3 = $m1->subtract($m2);

        $this->assertEquals(50, $m3->raw());
    }

    public function test_it_can_multiply_money()
    {
        $m1 = new Money(100, 'GBP');
        $m2 = $m1->multiply(1.5);

        $this->assertEquals(150, $m2->raw());
    }

    public function test_it_can_divide_money()
    {
        $m1 = new Money(100, 'GBP');
        $m2 = $m1->divide(3);

        // 100 / 3 = 33.333... -> 33
        $this->assertEquals(33, $m2->raw());
    }

    public function test_it_can_discount_money()
    {
        $m1 = new Money(1000, 'GBP'); // 10.00
        $m2 = $m1->discount(20); // 20% discount -> 80% remains

        $this->assertEquals(800, $m2->raw());
    }

    public function test_comparisons()
    {
        $m1 = new Money(100, 'GBP');
        $m2 = new Money(200, 'GBP');
        $m3 = new Money(100, 'GBP');

        $this->assertTrue($m2->isGreaterThan($m1));
        $this->assertTrue($m1->isLessThan($m2));
        $this->assertTrue($m1->isEqualTo($m3));
        $this->assertFalse($m1->isEqualTo($m2));
        $this->assertTrue($m2->isGreaterThanOrEqualTo($m1));
        $this->assertTrue($m1->isGreaterThanOrEqualTo($m3));
        $this->assertTrue($m1->isLessThanOrEqualTo($m2));
        $this->assertTrue($m1->isLessThanOrEqualTo($m3));
    }

    public function test_status_checks()
    {
        $this->assertTrue((new Money(0, 'GBP'))->isZero());
        $this->assertTrue((new Money(100, 'GBP'))->isPositive());
        $this->assertTrue((new Money(-100, 'GBP'))->isNegative());
    }

    public function test_sum()
    {
        $moneys = [
            new Money(100, 'GBP'),
            new Money(200, 'GBP'),
            new Money(300, 'GBP'),
        ];

        $sum = Money::sum($moneys, 'GBP');
        $this->assertEquals(600, $sum->raw());
    }

    public function test_it_formats_correctly()
    {
        $money = new Money(12345, 'GBP');
        // GBP format is £123.45 in en_GB
        $this->assertEquals('£123.45', $money->formatted('en_GB'));
    }

    public function test_json_serialization()
    {
        $money = new Money(100, 'GBP');
        $json = json_encode($money);
        $expected = json_encode([
            'amount' => 100,
            'currency' => 'GBP',
            'rounding_mode' => PHP_ROUND_HALF_UP,
        ]);
        $this->assertEquals($expected, $json);
    }

    public function test_it_is_wireable()
    {
        $money = new Money(100, 'GBP');
        $wire = $money->toLivewire();
        $this->assertEquals([
            'amount' => 100,
            'currency' => 'GBP',
            'rounding_mode' => PHP_ROUND_HALF_UP,
        ], $wire);

        $fromWire = Money::fromLivewire($wire);
        $this->assertEquals(100, $fromWire->raw());
        $this->assertEquals('GBP', $fromWire->currency());
    }

    public function test_money_helper()
    {
        $m = money(100);
        $this->assertInstanceOf(Money::class, $m);
        $this->assertEquals(100, $m->raw());
        $this->assertEquals('GBP', $m->currency()); // Default should be GBP
    }

    public function test_it_can_sum_inferred_currency()
    {
        $moneys = [
            new Money(100, 'USD'),
            new Money(200, 'USD'),
        ];

        $sum = Money::sum($moneys);
        $this->assertEquals(300, $sum->raw());
        $this->assertEquals('USD', $sum->currency());
    }

    public function test_sum_mismatch_exception()
    {
        $moneys = [
            new Money(100, 'USD'),
            new Money(200, 'GBP'),
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Currency mismatch in sum().');

        Money::sum($moneys);
    }

    public function test_clone_works_without_recursion()
    {
        $money = new Money(100, 'GBP');
        $cloned = clone $money;
        $this->assertEquals(100, $cloned->raw());
        $this->assertNotSame($money, $cloned);
    }
}
