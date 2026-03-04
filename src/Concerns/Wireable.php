<?php

declare(strict_types=1);

namespace AyupCreative\Casts\Currency\Concerns;

trait Wireable
{
    /**
     * Get the value that should be stored by Livewire.
     *
     * @return array
     */
    public function toLivewire()
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'rounding_mode' => $this->rounding_mode,
        ];
    }

    /**
     * Create an instance from the value stored by Livewire.
     *
     * @param array $value
     * @return static
     */
    public static function fromLivewire($value): static
    {
        return new static(...$value);
    }
}
