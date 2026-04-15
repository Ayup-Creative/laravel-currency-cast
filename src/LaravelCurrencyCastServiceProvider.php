<?php

namespace AyupCreative\Casts\Currency;

use Illuminate\Support\ServiceProvider;

class LaravelCurrencyCastServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/currency.php', 'currency');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ .'/../config/currency.php' => config_path('currency.php')
        ], 'currency-config');
    }
}
