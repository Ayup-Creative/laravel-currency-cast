<?php

namespace AyupCreative\Casts\Currency;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use AyupCreative\Casts\Currency\Values\Money;

class Currency implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): ?Money
    {
        if ($value === null) {
            return null;
        }

        $column = match(true) {
            method_exists($model, 'getCurrencyCode') => $model->getCurrencyCode(),
            property_exists($model, 'currencyCode') => $model->currencyCode,
            default => config('currency.currency_code')
        };

        $column = value($column);

        $currency = $model->hasAttribute($column) ? $model->{$column} : $column;

        return app(Money::class, ['amount' => $value, 'currency' => $currency]);
    }

    public function set($model, string $key, $value, array $attributes): array
    {
        if ($value === null) {
            return [$key => null];
        }

        if ($value instanceof Money) {
            return [$key => $value->raw()];
        }

        // Allow floats / numeric strings
        return [$key => (int) round(((float) $value) * 100)];
    }
}
