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
        $roundingMode = $this->rounding_mode;

        if (is_object($roundingMode) && enum_exists(\RoundingMode::class) && $roundingMode instanceof \RoundingMode) {
            $roundingMode = [
                'enum' => \RoundingMode::class,
                'case' => $roundingMode->name,
            ];
        }

        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'rounding_mode' => $roundingMode,
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
        if (isset($value['rounding_mode']) && is_array($value['rounding_mode']) && isset($value['rounding_mode']['enum']) && $value['rounding_mode']['enum'] === \RoundingMode::class) {
            if (enum_exists(\RoundingMode::class)) {
                $value['rounding_mode'] = constant("\RoundingMode::{$value['rounding_mode']['case']}");
            }
        }

        return new static(...$value);
    }
}
