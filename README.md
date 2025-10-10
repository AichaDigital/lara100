# Lara100 - Base-100 Cast for Laravel

[![Tests](https://img.shields.io/github/actions/workflow/status/aichadigital/lara100/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/aichadigital/lara100/actions?query=workflow%3Atests+branch%3Amain)
[![Code Style](https://img.shields.io/github/actions/workflow/status/aichadigital/lara100/pint.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/aichadigital/lara100/actions?query=workflow%3Apint+branch%3Amain)
[![PHPStan](https://img.shields.io/github/actions/workflow/status/aichadigital/lara100/phpstan.yml?branch=main&label=phpstan&style=flat-square)](https://github.com/aichadigital/lara100/actions?query=workflow%3Aphpstan+branch%3Amain)
[![Latest Version](https://img.shields.io/github/v/release/aichadigital/lara100?style=flat-square)](https://github.com/aichadigital/lara100/releases)

> **Note:** Package will be published to Packagist soon. Once published, additional badges for Packagist version and downloads will be added.

A Laravel package that provides a custom Eloquent cast for handling monetary/decimal values by storing them as integers (cents) in the database while working with decimals in your PHP code, eliminating floating-point precision errors.

## Why Lara100?

Floating-point arithmetic in PHP (and most programming languages) can lead to precision errors:

```php
0.1 + 0.2 === 0.3  // false! 😱
// Result: 0.30000000000000004
```

This is particularly problematic when dealing with:
- 💰 **Monetary values** (prices, amounts, balances)
- 📊 **Percentages** (tax rates, discounts)
- 📏 **Any centesimal measurements**

**Lara100** solves this by storing values as integers (cents) in the database, while letting you work with familiar decimal values in your code.

```php
// In your database: 1999 (integer - cents)
// In your application: 19.99 (decimal - dollars/euros)
```

## Installation

You can install the package via Composer:

```bash
composer require aichadigital/lara100
```

## Requirements

- PHP 8.3 or 8.4
- Laravel 11.x or 12.x

## Usage

### Basic Usage (Cast)

Apply the `Base100` cast to your model attributes:

```php
use AichaDigital\Lara100\Casts\Base100;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected function casts(): array
    {
        return [
            'price' => Base100::class,
            'cost'  => Base100::class,
            'tax'   => Base100::class,
        ];
    }
}
```

Now you can work with decimals in your application while storing integers in the database:

```php
$product = new Product;
$product->price = 19.99;  // You set: 19.99 (decimal)
$product->save();         // DB stores: 1999 (integer cents)

echo $product->price;     // You get: 19.99 (decimal)

// Arithmetic operations work perfectly with decimals
$total = $product->price + $product->tax;  // 19.99 + 2.50 = 22.49 ✅
```

### Advanced Usage (Trait)

For convenience, you can use the `HasBase100` trait to apply the cast to multiple attributes at once:

```php
use AichaDigital\Lara100\Concerns\HasBase100;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasBase100;

    protected function base100Attributes(): array
    {
        return ['price', 'cost', 'tax', 'discount'];
    }
}
```

The trait automatically applies the `Base100` cast to all specified attributes.

## How It Works

### Database → Application (GET)
```php
// Database stores: 1999 (INTEGER cents)
// Cast converts to: 19.99 (DECIMAL)
$product->price;  // 19.99
```

### Application → Database (SET)
```php
// Application receives: 19.99 (DECIMAL)
// Cast converts to: 1999 (INTEGER cents)
$product->price = 19.99;
$product->save();  // Stores 1999 in DB
```

## Examples

### Working with Monetary Values

```php
$invoice = new Invoice;
$invoice->subtotal = 100.00;  // You set: $100.00 (decimal)
$invoice->tax = 13.00;        // You set: $13.00 (decimal)
$invoice->total = 113.00;     // You set: $113.00 (decimal)
$invoice->save();             // DB stores: 10000, 1300, 11300 (integers)

// Calculate percentage (works naturally with decimals)
$taxRate = ($invoice->tax / $invoice->subtotal) * 100;  // 13%

// Display to user (already a decimal!)
$formatted = '$' . number_format($invoice->total, 2);  // "$113.00"
```

### Performing Calculations

```php
$product = Product::find(1);  // price = 19.99 (DB has 1999)
$quantity = 3;

$lineTotal = $product->price * $quantity;  // 59.97 (19.99 × 3)
$discount = 5.00;                          // $5.00 discount
$finalTotal = $lineTotal - $discount;      // 54.97 ✅

// Works naturally with decimal arithmetic!
```

### Handling Edge Cases

```php
// Zero values
$product->price = 0.00;  // Stores 0 in DB

// Negative values (refunds, discounts)
$refund->amount = -25.00;  // Stores -2500 in DB (negative cents)

// Large numbers
$property->price = 500000.00;  // Stores 50000000 in DB ($500,000.00)
```

## Database Schema

Your database columns should be defined as `INTEGER` (to store cents):

```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->integer('price')->default(0);  // Stores cents: 1999 = $19.99
    $table->integer('cost')->default(0);   // Stores cents: 1500 = $15.00
    $table->integer('tax')->default(0);    // Stores cents: 250 = $2.50
    $table->timestamps();
});
```

**Why INTEGER instead of DECIMAL?**
- ✅ Better performance (integer operations are faster)
- ✅ Less storage space
- ✅ No floating-point precision issues at database level
- ✅ Compatible with all database engines

## Comparison with Alternatives

| Solution | DB Column Type | PHP Value Type | Precision | Package Size |
|----------|----------------|----------------|-----------|--------------|
| **Lara100** | `INTEGER` (cents) | `float` (19.99) | ✅ Perfect | Lightweight cast |
| [moneyphp/money](https://github.com/moneyphp/money) | `INTEGER` (cents) | `Money` object | ✅ Perfect | Full-featured library |
| [brick/money](https://github.com/brick/money) | `INTEGER` (cents) | `Money` object | ✅ Perfect | Full-featured library |
| Native DECIMAL | `DECIMAL(10,2)` | `float` (19.99) | ⚠️ Precision issues | No package needed |

**Key Differences:**

- **Lara100**: Lightweight cast that stores integers in DB (efficient), but lets you work with decimals in PHP (natural)
- **Money libraries**: Full-featured libraries with objects, currency conversion, formatting, etc.
- **Native DECIMAL**: Traditional approach, works with floats in PHP (precision issues)

**Choose Lara100 when:**
- ✅ You want a simple, Laravel-native solution
- ✅ You prefer working with familiar decimal values (19.99)
- ✅ You want efficient integer storage in the database
- ✅ You don't need currency conversions or complex money operations
- ✅ You want to avoid float precision errors without heavy dependencies

**Consider alternatives when:**
- ❌ You need multi-currency support
- ❌ You need complex monetary operations (allocation, distribution, rounding strategies)
- ❌ You prefer working with Money objects instead of scalar decimals
- ❌ You need advanced formatting and localization features

## Testing

The package includes comprehensive tests for both the cast and trait:

```bash
composer test
```

Run tests with coverage:

```bash
composer test-coverage
```

Run tests in parallel:

```bash
composer test-parallel
```

## Code Quality

Run PHPStan static analysis:

```bash
composer phpstan
```

Run Laravel Pint code formatter:

```bash
composer format
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

AichaDigital is a ITt company focused on IT services.


