<?php

namespace AyupCreative\Casts\Currency\Values;

use AyupCreative\Casts\Currency\Contracts\Tax;
use Exception;
use JetBrains\PhpStorm\Pure;
use \NumberFormatter;

/**
 * Represents a monetary value with associated currency and tax configuration.
 *
 * This class provides functionality to handle monetary computations, such as
 * rounding, formatting, addition, and subtraction, while maintaining currency
 * integrity. It also supports tax calculations based on configuration settings.
 */
final class Money implements \Stringable
{
    /**
     * Initializes a new instance of the class with the specified parameters.
     *
     * @param int $amount The amount value.
     * @param string $currency The currency code, default is 'GBP'.
     * @param int $rounding_mode The rounding mode, default is PHP_ROUND_HALF_UP.
     * @param bool $includes_tax Indicates whether the amount includes tax, default is false.
     */
    public function __construct(
        private readonly int $amount,
        private readonly string $currency = 'GBP',
        private readonly int $rounding_mode = PHP_ROUND_HALF_UP
    ) {}

    /**
     * Retrieves the raw integer value of the amount.
     *
     * @return int The raw amount value.
     */
    public function raw(): int
    {
        return $this->amount;
    }

    /**
     * Retrieves the computed value by dividing the amount by 100 and rounding the result.
     *
     * @return float The rounded value of the computation.
     */
    public function value(): float
    {
        return $this->round($this->amount / 100);
    }

    /**
     * Retrieves the currency associated with the object.
     *
     * @return string The currency value.
     */
    public function currency(): string
    {
        return $this->currency;
    }

    /**
     * Adds the provided object to the current instance.
     *
     * @param self $other The object to add, which must have the same currency as the current instance.
     * @return self A new instance representing the sum of the amounts of both objects while maintaining other properties.
     */
    public function add(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self(
            $this->amount + $other->amount,
            $this->currency,
            $this->rounding_mode
        );
    }

    /**
     * Subtracts the given object from the current object.
     *
     * @param self $other The object to subtract from the current instance.
     * @return self A new instance representing the result of the subtraction.
     */
    public function subtract(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self(
            $this->amount - $other->amount,
            $this->currency,
            $this->rounding_mode
        );
    }

    /**
     * Divides the current amount by the given divisor and returns a new instance.
     *
     * @param int|float $divisor The value to divide the amount by.
     * @return self A new instance with the divided amount.
     */
    public function divide(float|int $divisor): self
    {
        if ($divisor <= 0) {
            throw new \InvalidArgumentException('Divisor must be greater than zero.');
        }

        $result = ((float) $this->amount) / (float) $divisor;
        $value = (int) round($result, 0, $this->rounding_mode);

        return new self(
            $value,
            $this->currency,
            $this->rounding_mode
        );
    }

    /**
     * Multiplies the current amount by the given multiplier and returns a new instance.
     *
     * @param int|float $multiplier The value by which to multiply the amount.
     * @return self A new instance with the multiplied amount.
     */
    public function multiply(float|int $multiplier): self
    {
        if ($multiplier < 0) {
            throw new \InvalidArgumentException('Multiplier must be non-negative.');
        }

        $result = ((float) $this->amount) * (float) $multiplier;
        $value = (int) round($result, 0, $this->rounding_mode);


        return new self(
            $value,
            $this->currency,
            $this->rounding_mode
        );
    }

    /**
     * "Discounts" the current amount by the given "per cent" and returns a new instance.
     *
     * @param int $percent
     * @return self A new instance with the multiplied amount.
     *
     * @throws Exception
     * @see self::multiply()
     */
    public function discount(int $percent): self
    {
        if($percent < 0) {
            throw new \Exception('Cannot discount by a negative value.');
        }

        if($percent === 0) {
            return clone $this;
        }

        $multiplier = (float)(1-($percent/100));

        return $this->multiply($multiplier);
    }

    /**
     * Creates and returns a shallow copy of the object.
     *
     * @return Money
     */
    public function __clone()
    {
        return clone $this;
    }

    /**
     * Sums a collection of currency objects and returns a new currency object.
     *
     * This method calculates the total amount of all currency objects in the provided iterable,
     * ensuring that all objects are of the same currency type. If a currency mismatch or an invalid
     * object type is encountered, an exception is thrown.
     *
     * @param iterable $currencies The collection of currency objects to sum.
     * @param string $currency The expected currency code (default is 'GBP').
     * @param int $rounding_mode The rounding mode to use for the resulting currency.
     *
     * @return self A new currency object representing the total sum.
     * @throws \InvalidArgumentException If any element in the iterable is not a currency object or if
     *                                   there is a currency mismatch.
     *
     */
    public static function sum(
        iterable $currencies,
        string $currency = 'GBP',
        int $rounding_mode = PHP_ROUND_HALF_UP
    ): self
    {
        $total = 0;

        foreach ($currencies as $money) {
            if (! $money instanceof self) {
                throw new \InvalidArgumentException('All values must be Currency objects.');
            }

            if ($money->currency !== $currency) {
                throw new \InvalidArgumentException('Currency mismatch in sum().');
            }

            $total += $money->amount;
        }

        return new self($total, $currency, $rounding_mode);
    }

    /**
     * Formats the monetary value using the specified locale.
     *
     * @param string|null $locale The locale to be used for formatting (default is 'en_GB').
     * @return string The formatted currency string.
     */
    public function formatted(?string $locale = 'en_GB'): string
    {
        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);

        return $formatter->formatCurrency(
            $this->value(),
            $this->currency,
        );
    }

    /**
     * Converts the object to its string representation.
     *
     * @return string The formatted string representation of the object.
     */
    public function __toString(): string
    {
        return $this->formatted();
    }

    /**
     * Rounds a given float value to specified precision using the rounding mode.
     *
     * @param float $value The value to round.
     *
     * @return float The rounded value.
     */
    private function round(float $value): float
    {
        return round($value, precision: 2, mode: $this->rounding_mode);
    }

    /**
     * Ensure the provided Currency object has the same currency type.
     *
     * @param self $other The other Currency object to compare with.
     *
     * @throws \InvalidArgumentException If the currencies do not match.
     */
    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException(
                "Currency mismatch: {$this->currency} vs {$other->currency}"
            );
        }
    }
}
