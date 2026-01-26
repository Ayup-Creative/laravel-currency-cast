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

        return app(Money::class, ['value' => $value]);
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
