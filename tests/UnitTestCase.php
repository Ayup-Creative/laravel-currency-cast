<?php

namespace AyupCreative\Casts\Currency\Tests;

use AyupCreative\Casts\Currency\LaravelCurrencyCastServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class UnitTestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            LaravelCurrencyCastServiceProvider::class,
        ];
    }
}
