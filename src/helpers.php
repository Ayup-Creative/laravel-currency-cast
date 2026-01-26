<?php

use AyupCreative\Casts\Currency\Values\Money;

if(!function_exists('money')) {
    /**
     * Converts a given amount into a Money value object.
     *
     * @param int $amount The monetary amount
     *
     * @return Money The resulting Money value object.
     */
    function money(int $amount): Money
    {
        return app(Money::class, compact('amount'));
    }
}
