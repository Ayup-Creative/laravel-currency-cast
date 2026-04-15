<?php

use AyupCreative\Casts\Currency\Values\Money;

if(!function_exists('money')) {
    /**
     * Converts a given amount into a Money value object.
     *
     * @param int $amount The monetary amount
     * @param string|null $currency The currency code (optional)
     *
     * @return Money The resulting Money value object.
     */
    function money(int $amount, ?string $currency = null): Money
    {
        $params = ['amount' => $amount];

        if ($currency !== null) {
            $params['currency'] = $currency;
        }

        return app(Money::class, $params);
    }
}
