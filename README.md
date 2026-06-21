# Lara100 — Exact Decimal for Laravel

[![Tests](https://img.shields.io/github/actions/workflow/status/aichadigital/lara100/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/aichadigital/lara100/actions?query=workflow%3Atests+branch%3Amain)
[![Code Style](https://img.shields.io/github/actions/workflow/status/aichadigital/lara100/pint.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/aichadigital/lara100/actions?query=workflow%3Apint+branch%3Amain)
[![PHPStan](https://img.shields.io/github/actions/workflow/status/aichadigital/lara100/phpstan.yml?branch=main&label=phpstan&style=flat-square)](https://github.com/aichadigital/lara100/actions?query=workflow%3Aphpstan+branch%3Amain)
[![Latest Version](https://img.shields.io/github/v/release/aichadigital/lara100?style=flat-square)](https://github.com/aichadigital/lara100/releases)

A Laravel package providing an **immutable, scale-configurable exact decimal value object** (`FixedDecimal`) and a matching Eloquent cast (`FixedDecimalCast`). Values are stored as plain integers (unscaled) in the database; the cast and value object handle all precision arithmetic using `brick/math` under the hood.

## Why Lara100?

Floating-point arithmetic is not safe for monetary or fiscal values:

```php
0.1 + 0.2 === 0.3;  // false — result is 0.30000000000000004
```

`FixedDecimal` eliminates this class of error entirely:

```php
use AichaDigital\Lara100\ValueObjects\FixedDecimal;

$a = FixedDecimal::ofDecimalString('0.1');
$b = FixedDecimal::ofDecimalString('0.2');
$c = $a->plus($b);

$c->toDecimalString();  // '0.3' — exact, always
```

Arithmetic is exact at every step. The result serialises as a decimal string (`'0.3'`), not a float, so API consumers and hash-based fiscal systems (AEAT Verifactu, etc.) always receive the canonical value.

## Requirements

- PHP 8.3 or 8.4
- Laravel 12.x or 13.x
- `brick/math` ^0.14.2 (pulled in automatically)

## Installation

```bash
composer require aichadigital/lara100
```

No configuration file is required for the `FixedDecimal` path. The legacy `config/lara100.php` (deprecated since 1.3.0) is still publishable for projects that still use the `Base100` cast.

## Usage

### 1. Database schema

Store unscaled integers. The scale is declared at the model level, not in the column:

```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->integer('price')->default(0);      // scale 2: 1999 = 19.99
    $table->integer('exchange_rate')->default(0); // scale 4: 12345 = 1.2345
    $table->timestamps();
});
```

### 2. Model casts

Declare the cast with the scale as a colon-separated suffix. The scale is **required**; omitting it throws immediately rather than silently misinterpreting the column:

```php
use AichaDigital\Lara100\Casts\FixedDecimalCast;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected function casts(): array
    {
        return [
            'price'         => FixedDecimalCast::class.':2',  // 1999 → 19.99
            'exchange_rate' => FixedDecimalCast::class.':4',  // 12345 → 1.2345
        ];
    }
}
```

### 3. Reading and writing

The cast accepts only `FixedDecimal|null` on assignment. Scalars are rejected deliberately — callers must choose the conversion explicitly:

```php
// Reading: the cast returns a FixedDecimal built from the stored integer
$product = Product::find(1);
$price = $product->price;           // FixedDecimal('19.99', scale=2)
$price->toDecimalString();          // '19.99'
$price->unscaledValue();            // 1999
$price->scale();                    // 2
echo json_encode($price);           // "19.99"  (exact decimal string, not float)

// Writing: build a FixedDecimal explicitly, then assign it
$product->price = FixedDecimal::ofDecimalString('29.99', 2);
$product->save();                   // DB stores 2999 (integer)

// null is allowed for nullable columns
$product->price = null;
$product->save();                   // DB stores NULL
```

Assigning a raw scalar throws `InvalidFixedDecimal` — the strict contract prevents accidental precision loss at the boundary:

```php
$product->price = 19.99;  // throws InvalidFixedDecimal — use ofDecimalString / ofFloat
```

### 4. Constructing FixedDecimal

**Recommended constructors:**

```php
use AichaDigital\Lara100\ValueObjects\FixedDecimal;

// From the raw stored integer (no conversion, no rounding — the cleanest path)
$price = FixedDecimal::ofUnscaled(1999, 2);   // 19.99
$rate  = FixedDecimal::ofUnscaled(12345, 4);  // 1.2345

// From a decimal string (parse and optionally coerce to a target scale)
$price = FixedDecimal::ofDecimalString('19.99');       // scale inferred as 2
$price = FixedDecimal::ofDecimalString('19.9', 2);     // coerced to scale 2 → '19.90'

// Zero at a given scale
$zero = FixedDecimal::zero(2);  // 0.00
```

**Migration-only boundary (not for new code):**

```php
use AichaDigital\Lara100\RoundingMode;

// From a float — reintroduces IEEE-754 at the boundary; use only when crossing
// a legacy float API or when receiving user input that cannot be treated as a string
$price = FixedDecimal::ofFloat(19.99, 2, RoundingMode::HalfUp);
```

### 5. Arithmetic

All arithmetic produces a new `FixedDecimal`; the original is unchanged (immutable):

```php
$price    = FixedDecimal::ofDecimalString('19.99');
$tax_rate = FixedDecimal::ofDecimalString('0.21');

$tax   = $price->multipliedBy($tax_rate)->toScale(2, RoundingMode::HalfUp);
$total = $price->plus($tax);

$tax->toDecimalString();    // '4.20'
$total->toDecimalString();  // '24.19'

// Division always requires an explicit scale and rounding mode
$half = $total->dividedBy(2, 2, RoundingMode::HalfUp);
$half->toDecimalString();   // '12.10'
```

Available arithmetic: `plus`, `minus`, `multipliedBy`, `dividedBy`, `toScale`, `negated`, `abs`.

### 6. Comparison

```php
$a = FixedDecimal::ofDecimalString('10.00');
$b = FixedDecimal::ofDecimalString('20.00');

$a->isEqualTo($b);      // false
$a->isLessThan($b);     // true
$a->isGreaterThan($b);  // false
$a->isZero();           // false
$a->isPositive();       // true
$a->isNegative();       // false
$a->compareTo($b);      // -1
```

### 7. Output

```php
$price = FixedDecimal::ofUnscaled(1999, 2);

$price->toDecimalString();   // '19.99'   — exact, preferred for persistence / hashing
$price->unscaledValue();     // 1999      — raw stored integer
$price->scale();             // 2
$price->toFloat();           // 19.99     — reintroduces float imprecision; display/legacy only
$price->toBigDecimal();      // Brick\Math\BigDecimal — escape hatch for raw brick/math use
json_encode($price);         // "19.99"   — jsonSerialize() returns the decimal string
```

### 8. RoundingMode

lara100 ships its own `RoundingMode` enum — fully encapsulated from the underlying `brick/math` engine:

| Case | Description | Typical use |
|------|-------------|-------------|
| `HalfUp` | 0.5 rounds away from zero | **EU/Spain fiscal standard; default** |
| `HalfEven` | 0.5 rounds to nearest even | Banker's rounding; accounting |
| `HalfDown` | 0.5 rounds toward zero | Conservative rounding |
| `Up` | Always away from zero | Always round up |
| `Down` | Always toward zero (truncate) | Always truncate |
| `Ceiling` | Toward positive infinity | |
| `Floor` | Toward negative infinity | |

`HalfUp` is the European/Spanish fiscal standard and the default wherever lara100 accepts a rounding mode. `HalfEven` (banker's rounding) is the right choice for general accounting aggregations.

Note: the legacy `Base100` cast exposed `PHP_ROUND_HALF_ODD` (mode 4). That mode has **no equivalent** in `FixedDecimal`. It is absent from the industry standards (java.math, IEEE 754, most fiscal specs) and from brick/math. `HalfUp` or `HalfEven` cover all fiscal cases.

## Positioning

lara100 is a **Laravel-native, scale-aware exact decimal** built on top of `brick/math`. It occupies a specific niche — here is when to reach for each option:

| Library | Abstraction | Laravel cast | Currency | When to use |
|---------|-------------|:------------:|:--------:|-------------|
| **lara100** | `FixedDecimal` (exact, scale-aware) | Yes (`FixedDecimalCast`) | No | Prices, rates, fiscal amounts in Laravel — exact arithmetic without currency overhead |
| `brick/math` | `BigDecimal`, `BigInteger` | No | No | Raw exact arithmetic outside Laravel; lara100 builds on this |
| `brick/money` | `Money` (amount + currency) | No | Yes | Multi-currency systems, allocation, currency conversion |
| `moneyphp/money` | `Money` (amount + currency) | No | Yes | Same as brick/money; wider ecosystem adoption |

**Choose lara100 when:**

- You are building a Laravel application and want Eloquent models to carry `FixedDecimal` attributes directly
- You need exact arithmetic over scale-aware values (prices at scale 2, VAT rates at scale 4, etc.)
- You do not need multi-currency support or allocation algorithms

**Reach for brick/math directly when:**

- You need exact arithmetic outside of Laravel (CLI tools, pure PHP libraries, etc.)
- You need `BigInteger` or `BigRational` (lara100 only wraps `BigDecimal`)

**Reach for brick/money or moneyphp/money when:**

- You need currency codes, currency conversion, or monetary allocation (splitting a total into N parts that sum exactly)

lara100 encapsulates `brick/math ^0.14.2` internally. The dependency is lightweight; `brick` types are not exposed except via the explicit `toBigDecimal()` escape hatch. Starting with 1.3.0 the "zero external dependencies" claim no longer applies — lara100 is still lightweight but it does pull in `brick/math`.

## Comparison with Alternatives

| Solution | DB column | PHP value | Precision | Laravel cast | Multi-currency |
|----------|-----------|-----------|-----------|:------------:|:--------------:|
| **lara100 `FixedDecimal`** | `INTEGER` (unscaled) | `FixedDecimal` (exact) | Exact | Yes | No |
| **lara100 `Base100`** (deprecated) | `INTEGER` (cents) | `float` | Float — imprecise | Yes | No |
| `brick/math` (`BigDecimal`) | — | `BigDecimal` (exact) | Exact | No | No |
| `brick/money` | `INTEGER` | `Money` (exact + currency) | Exact | No | Yes |
| `moneyphp/money` | `INTEGER` | `Money` (exact + currency) | Exact | No | Yes |
| Native `DECIMAL` column | `DECIMAL(10,2)` | `float` | Float — imprecise | No | No |

## Legacy / Deprecated

### `Base100` cast and `HasBase100` trait (deprecated since 1.3.0, removed in 2.0.0)

The original `Base100` cast converts stored integers to PHP `float` on read. While the integer storage is correct, the float representation reintroduces IEEE-754 imprecision in the application layer — precisely the problem lara100 was designed to solve.

**Migration path:**

Replace `Base100` with `FixedDecimalCast` at the scale your column uses (almost always 2 for cent-based money):

```php
// Before (deprecated)
use AichaDigital\Lara100\Casts\Base100;
use AichaDigital\Lara100\Concerns\HasBase100;

class Invoice extends Model
{
    use HasBase100;

    protected function base100Attributes(): array
    {
        return ['subtotal', 'tax_amount', 'total'];
    }
}

// After (1.3.0+)
use AichaDigital\Lara100\Casts\FixedDecimalCast;

class Invoice extends Model
{
    protected function casts(): array
    {
        return [
            'subtotal'   => FixedDecimalCast::class.':2',
            'tax_amount' => FixedDecimalCast::class.':2',
            'total'      => FixedDecimalCast::class.':2',
        ];
    }
}
```

After the migration, attributes return `FixedDecimal` objects instead of floats. Update any code that assigned a raw float to these attributes — use `FixedDecimal::ofDecimalString()` or `FixedDecimal::ofUnscaled()` instead.

`Base100Int` (introduced in 1.1.0) is **not deprecated** — it remains valid for columns where you genuinely need the raw unscaled integer in the application layer.

The config keys `rounding_mode` and `use_bcmath` (in `config/lara100.php`) are deprecated since 1.3.0 and ignored by `FixedDecimal`. They remain present for `Base100` backward compatibility and will be removed in 2.0.0.

## Testing

```bash
composer test
composer test-coverage
composer test-parallel
```

## Code Quality

```bash
composer phpstan
composer pint
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Security Vulnerabilities

If you discover a security vulnerability, please send an e-mail to Abdelkarim Mateos Sanchez via [abdelkarim.mateos@castris.com](mailto:abdelkarim.mateos@castris.com).

## Credits

- [Abdelkarim Mateos Sanchez](https://github.com/aichadigital)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## About AichaDigital

AichaDigital is an IT company focused on IT services.
