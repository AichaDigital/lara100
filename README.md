# Lara100 - Base-100 Cast for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/aichadigital/lara100.svg?style=flat-square)](https://packagist.org/packages/aichadigital/lara100)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/aichadigital/lara100/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/aichadigital/lara100/actions?query=workflow%3Atests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/aichadigital/lara100/pint.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/aichadigital/lara100/actions?query=workflow%3Apint+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/aichadigital/lara100.svg?style=flat-square)](https://packagist.org/packages/aichadigital/lara100)

A Laravel package that provides a custom Eloquent cast for handling decimal values as base-100 integers (cents/centesimals), eliminating floating-point precision errors in your applications.

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

**Lara100** solves this by storing decimals as integers multiplied by 100 in your application, while keeping them as decimals in the database.

```php
// In your database: 19.99 (decimal)
// In your application: 1999 (integer)
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

Now you can work with integers in your application:

```php
$product = new Product;
$product->price = 1999;  // Stores 19.99 in database
$product->save();

echo $product->price;  // Returns 1999 (integer)

// Arithmetic operations work perfectly
$total = $product->price + $product->tax;  // Integer addition, no precision errors!
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
// Database stores: 19.99 (DECIMAL)
// Cast converts to: 1999 (INTEGER)
$product->price;  // 1999
```

### Application → Database (SET)
```php
// Application sends: 1999 (INTEGER)
// Cast converts to: 19.99 (DECIMAL)
$product->price = 1999;
$product->save();  // Stores 19.99 in DB
```

## Examples

### Working with Monetary Values

```php
$invoice = new Invoice;
$invoice->subtotal = 10000;  // $100.00
$invoice->tax = 1300;        // $13.00
$invoice->total = 11300;     // $113.00
$invoice->save();

// Calculate percentage
$taxRate = ($invoice->tax / $invoice->subtotal) * 100;  // 13%

// Display to user (format as needed)
$formatted = '$' . number_format($invoice->total / 100, 2);  // "$113.00"
```

### Performing Calculations

```php
$product = Product::find(1);  // price = 1999 (19.99)
$quantity = 3;

$lineTotal = $product->price * $quantity;  // 5997
$discount = 500;  // $5.00 discount
$finalTotal = $lineTotal - $discount;  // 5497 ($54.97)

// No floating-point errors! ✨
```

### Handling Edge Cases

```php
// Zero values
$product->price = 0;  // Stores 0.00 in DB

// Negative values (refunds, discounts)
$refund->amount = -2500;  // Stores -25.00 in DB

// Large numbers
$property->price = 50000000;  // Stores 500000.00 in DB ($500,000.00)
```

## Database Schema

Your database columns should be defined as `DECIMAL`:

```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->decimal('price', 10, 2);  // 10 digits, 2 decimals
    $table->decimal('cost', 10, 2);
    $table->decimal('tax', 10, 2);
    $table->timestamps();
});
```

## Comparison with Alternatives

| Solution | Storage | App Values | Precision | Package |
|----------|---------|------------|-----------|---------|
| **Lara100** | DECIMAL | Integer | ✅ Perfect | Lightweight |
| [moneyphp/money](https://github.com/moneyphp/money) | INTEGER | Object | ✅ Perfect | Full-featured, heavier |
| [brick/money](https://github.com/brick/money) | INTEGER | Object | ✅ Perfect | Full-featured, heavier |
| Native DECIMAL | DECIMAL | Float | ⚠️ Issues | None needed |

**Choose Lara100 when:**
- ✅ You want a simple, Laravel-native solution
- ✅ You prefer working with integers
- ✅ You want to keep decimals in your database
- ✅ You don't need currency conversions or complex money operations

**Consider alternatives when:**
- ❌ You need multi-currency support
- ❌ You need complex monetary operations (allocation, distribution)
- ❌ You prefer working with money objects instead of integers

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

AichaDigital is a software development company focused on creating high-quality Laravel packages and applications.

