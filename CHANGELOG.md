# Changelog

All notable changes to `lara100` will be documented in this file.

## 1.0.0 - 2025-10-10

### Added

- **Base100 Cast**: Eloquent cast that stores decimal values as integers (cents) in database
- **HasBase100 Trait**: Convenient trait for applying cast to multiple attributes
- **Configurable Rounding Modes**: Support for 4 PHP rounding modes
  - `PHP_ROUND_HALF_UP` (default - Spain/EU standard)
  - `PHP_ROUND_HALF_EVEN` (Banker's rounding for accounting)
  - `PHP_ROUND_HALF_DOWN`
  - `PHP_ROUND_HALF_ODD`
- **BCMath Support**: Optional support for arbitrary precision arithmetic
- **Configuration File**: Publishable `config/lara100.php`
- **Environment Variables**: `LARA100_ROUNDING_MODE` and `LARA100_USE_BCMATH`
- **Per-Attribute Override**: Override config per model attribute
- **Comprehensive Tests**: 22 tests with 59 assertions
- **GitHub Actions CI/CD**: Automated testing on PHP 8.3, Laravel 11 & 12
- **Complete Documentation**: README, VERSIONING_STRATEGY, IMPROVEMENTS guides

### Technical Details

- Database columns: `INTEGER` (stores cents/centesimals)
- Application values: `FLOAT` (works with decimals)
- Example: DB stores 1999 → App uses 19.99
- Support for PHP 8.3 and 8.4
- Support for Laravel 11 and 12
- PHPStan level 6 compliant
- Laravel Pint formatted

## 1.0.1 - 2025-10-11

### Modified
- **README.md**: Remove the notice about Packagist


