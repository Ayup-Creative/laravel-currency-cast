# Laravel Currency Cast

A PHP library for handling monetary values with integer-based precision, including a Laravel custom caster for Eloquent models.

## Features

- Integer-based monetary calculations to avoid floating-point errors.
- Automatic casting of database values to `Money` objects in Laravel.
- Supports different currencies and formatting via `intl`.
- Arithmetic operations (add, subtract, multiply, divide, discount, sum).
- Comparison operations (greater than, less than, equals, etc.).
- Livewire support via `Wireable` interface.
- JSON serialization.

## Installation

```bash
composer require ayup-creative/laravel-currency-cast
```

## Usage

### Money Value Object

You can create `Money` objects using the constructor or the `fromFloat` helper:

```php
use AyupCreative\Casts\Currency\Values\Money;

// Directly from raw cents
$price = new Money(1000, 'GBP'); // £10.00

// From a float value
$price = Money::fromFloat(19.99, 'USD'); // $19.99

// Using the helper
$price = money(500); // £5.00 (defaults to GBP)
```

#### Arithmetic Operations

```php
$m1 = money(1000); // £10.00
$m2 = money(500);  // £5.00

$sum = $m1->add($m2);        // £15.00
$diff = $m1->subtract($m2);  // £5.00
$multi = $m1->multiply(1.5); // £15.00
$div = $m1->divide(2);       // £5.00
$discounted = $m1->discount(20); // £8.00 (20% off)
```

#### Comparisons

```php
$m1 = money(1000);
$m2 = money(2000);

$m1->isLessThan($m2); // true
$m1->isGreatThan($m2); // false
$m1->isEqualTo(money(1000)); // true
$m1->isZero(); // false
$m1->isPositive(); // true
```

#### Formatting

```php
$money = money(12345); // £123.45
echo $money->formatted('en_GB'); // "£123.45"
echo $money->formatted('en_US'); // "£123.45" (Currency code is respected)
echo (string) $money; // Uses default en_GB
```

### Laravel Caster

To use the caster in your Eloquent models, add it to the `$casts` array:

```php
use AyupCreative\Casts\Currency\Currency;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $casts = [
        'price' => Currency::class,
    ];

    // Optional: Define currency code for the model
    public $currencyCode = 'USD';
    
    // OR use a method
    public function getCurrencyCode()
    {
        return 'EUR';
    }
}
```

The caster can also resolve the currency code from another attribute on the model:

```php
class Product extends Model
{
    protected $casts = [
        'price' => Currency::class,
    ];

    // Points to the 'currency' column in the database
    public $currencyCode = 'currency';
}
```

If neither `currencyCode` property nor `getCurrencyCode()` method exists, the caster will look for a configuration value: `config('currency.currency_code')`.

## Identified Bugs & Limitations

During the creation of the test suite, several issues were identified in the current implementation:

1.  **Rounding Bug in `Money::fromFloat()`**:
    The method currently uses `round($amount * 100, 2)` before casting to an integer. This results in incorrect rounding for values with more than 2 decimal places. For example, `Money::fromFloat(1.235)` results in `123` cents instead of `124`.
    
2.  **Infinite Recursion in `Money::__clone()`**:
    The `__clone()` method contains `return clone $this;`, which causes a stack overflow (infinite recursion) when the object is cloned.

3.  **Caster `set()` Behavior**:
    The `Currency` caster's `set()` method automatically multiplies any numeric value by 100. If you attempt to set an attribute using a value that is already in cents (e.g., `1000`), it will be stored as `100000` (e.g., £1000.00 instead of £10.00).

4.  **Negative Multiplier Restriction**:
    `Money::multiply()` and `Money::discount()` explicitly throw exceptions for negative values, which prevents use cases involving credit notes or reversing monetary values.

5.  **`Money::sum()` Currency Mismatch**:
    The `sum()` method defaults to `'GBP'`. If summing a collection of other currencies (e.g., `USD`), you must explicitly pass the currency code as the second argument, even if all objects in the collection are already `USD`.

7.  **Dead/Incorrect Imports**:
    The `LaravelCurrencyCastServiceProvider` imports non-existent classes (`Taxes\Tax` and `Contracts\Tax`), which could cause issues if the provider is ever expanded or if strict static analysis is used.

8.  **Missing Tax Implementation**:
    The docstrings in the `Money` class mention support for tax calculations, but there is currently no implementation of tax logic within the class.

## Testing

Run the test suite using PHPUnit:

```bash
composer test
```
