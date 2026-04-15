<?php

namespace AyupCreative\Casts\Currency\Tests\Integration;

use AyupCreative\Casts\Currency\Currency;
use AyupCreative\Casts\Currency\Tests\UnitTestCase;
use AyupCreative\Casts\Currency\Values\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CurrencyCastTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('test_models', function (Blueprint $table) {
            $table->id();
            $table->integer('price')->nullable();
            $table->string('currency_code')->nullable();
            $table->timestamps();
        });
    }

    public function test_it_casts_to_money_object()
    {
        $model = new TestModel();

        // FIX VERIFIED: When setting an integer 1000, the caster treats it as raw cents.
        $model->price = 1000;

        $this->assertInstanceOf(Money::class, $model->price);
        $this->assertEquals(1000, $model->price->raw());
        $this->assertEquals('GBP', $model->price->currency());
    }

    public function test_it_casts_from_money_object()
    {
        $model = new TestModel();
        $model->price = new Money(2000, 'GBP');

        $this->assertEquals(2000, $model->getAttributes()['price']);
    }

    public function test_it_casts_from_float()
    {
        $model = new TestModel();
        $model->price = 19.99;

        // 19.99 * 100 = 1999
        $this->assertEquals(1999, $model->getAttributes()['price']);
    }

    public function test_it_handles_null()
    {
        $model = new TestModel();
        $model->price = null;

        $this->assertNull($model->price);
        $this->assertNull($model->getAttributes()['price']);
    }

    public function test_it_can_resolve_currency_from_method()
    {
        $model = new TestModelWithMethod();
        $model->price = 1000;

        $this->assertEquals('USD', $model->price->currency());
    }

    public function test_it_can_resolve_currency_from_property()
    {
        $model = new TestModelWithProperty();
        $model->price = 1000;

        $this->assertEquals('EUR', $model->price->currency());
    }

    public function test_it_can_resolve_currency_from_other_attribute()
    {
        $model = new TestModelWithAttribute();
        $model->price = 1000;
        $model->currency_code = 'JPY';

        $this->assertEquals('JPY', $model->price->currency());
    }
}

class TestModel extends Model
{
    protected $table = 'test_models';
    protected $casts = [
        'price' => Currency::class,
    ];

    public $currencyCode = 'GBP';
}

class TestModelWithMethod extends Model
{
    protected $table = 'test_models';
    protected $casts = [
        'price' => Currency::class,
    ];

    public function getCurrencyCode()
    {
        return 'USD';
    }
}

class TestModelWithProperty extends Model
{
    protected $table = 'test_models';
    protected $casts = [
        'price' => Currency::class,
    ];

    public $currencyCode = 'EUR';
}

class TestModelWithAttribute extends Model
{
    protected $table = 'test_models';
    protected $casts = [
        'price' => Currency::class,
    ];

    public $currencyCode = 'currency_code';
}
