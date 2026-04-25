# Changelog

All notable changes to `lara100` will be documented in this file.

## 1.2.1 - 2026-04-25

### Maintenance

- **CI/CD hardening**: align with AichaDigital convention (mustache, lararoi, lara-verifactu)
  - Add `.github/dependabot.yml` — weekly bumps for `composer` and `github-actions`
  - Add `.github/workflows/dependabot-auto-merge.yml` — auto-squash for `semver-minor` and `semver-patch` updates
  - Branch protection enabled on `main` (mirror of lararoi: `enforce_admins`, `linear_history`, `block_creations`, no required reviews for single-dev)
- **GitHub Actions**: bump `actions/checkout` to v6 across all workflows
- **Dependencies (via Dependabot)**: bump `dependabot/fetch-metadata` from 2.4.0 to 3.1.0
- **PHPStan baseline**: regenerate for Pest 4 `markTestSkipped` signature

### Fixed

- **Packagist webhook**: realign endpoint to `/api/github` and rotate to the working AichaDigital token. Previous webhook had been silently inactive for 6 months (`last_response: unused`), causing v1.2.0 to never be published until manual force-update on this date.

No runtime/code changes. All updates are tooling and infrastructure.

## 1.2.0 - 2026-04-21

### Added

- **Laravel 13 support**: `illuminate/contracts` and `illuminate/database` now accept `^12.0||^13.0`
- **CI matrix**: extended to Laravel 12.* + Laravel 13.* across PHP 8.3 and 8.4

### Changed

- **`orchestra/testbench`**: bumped to `^10.6||^11.0`

### Removed

- **Laravel 11 support** (EOL): dropped from `illuminate/*` constraints

### Compatibility

| | Supported |
|---|---|
| PHP | 8.3, 8.4 |
| Laravel | 12, 13 |
| Testbench | 10.6+, 11 |

## 1.1.0 - 2025-11-23

### Added

- **Base100Int Cast**: New cast that maintains integer values throughout application lifecycle
  - Prevents floating-point precision errors in financial calculations
  - Essential for fiscal compliance (AEAT/Verifactu)
  - Works with integer values in both database AND application
  - Example: DB stores 1999 → App uses 1999 (not 19.99)
- **Comprehensive Testing**: 169 new tests for Base100Int cast
  - Financial calculation precision tests
  - Edge case coverage
  - Rounding mode validation
- **Release Workflow Documentation**: Complete guide at `.github/RELEASE_WORKFLOW.md`
  - Step-by-step release process with `gh` CLI
  - Quality gates checklist
  - Useful shell aliases
  - Troubleshooting guide

### Fixed

- **Float Precision Issue**: Resolved critical precision problem with Base100 cast (see `ISSUE_BASE100_FLOAT.md`)
- **Packagist Configuration**: Prevent development branches from appearing in Packagist
  - Added `non-feature-branches` configuration
  - Branch alias mapping `dev-main` to `1.x-dev`

### Technical Details

- New file: `src/Casts/Base100Int.php` (67 lines)
- New tests: `tests/Unit/Casts/Base100IntTest.php` (169 tests)
- Updated phpstan baseline for test edge cases
- Maintained 100% code coverage
- All quality gates passing (PHPStan MAX, Pest)

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

## 1.0.2 - 2025-10-11

### 🎯 Enterprise Quality System - Complete Refactor

#### Added
- **PHPStan MAX Level**: Upgraded from level 6 to MAX (9) with Bleeding Edge
- **Mutation Testing**: Implemented with Pest Mutate - 72.73% score
- **100% Code Coverage**: Perfect code coverage achieved
- **Quality System**: Complete pre-commit and quality check system
- **SOLID Principles**: All principles applied and documented
- **Comprehensive Documentation**:
  - `QUALITY.md`: Complete quality system guide (351 lines)
  - `MUTATIONS.md`: Mutation testing analysis (200+ lines)
  - `Good_Code_en_Laravel.md`: Good Code principles analysis (405 lines)

#### Enhanced Testing
- **Tests**: 22 → 129 tests (+486% increase)
- **Assertions**: 61 → 489 assertions (+702% increase)
- **Test Suites**: 3 → 10 comprehensive test suites
- **New Test Files**:
  - `Base100BcmathTest.php`: BCMath-specific testing (7 tests)
  - `Base100EdgeCasesTest.php`: Edge cases coverage (12 tests)
  - `Base100MutationTest.php`: Mutation targeting (11 tests)
  - `Base100KillMutationsTest.php`: Mutation killers (19 tests)
  - `Base100FinalPushTest.php`: Final push tests (14 tests)
  - `Base100CoverageTest.php`: Branch coverage (11 tests)
  - `Base100UltraKillerTest.php`: Ultra-specific mutations (15 tests)
  - `Base100AtomicKillerTest.php`: Atomic detection (10 tests)
  - `Base100TypeStrictnessTest.php`: Type strictness (8 tests)

#### Quality Improvements
- **PHPStan**: Level MAX + Bleeding Edge, 0 errors
- **Code Coverage**: 100.0% (from ~95%)
- **Mutation Score**: 72.73% (excellent for mathematical code)
- **Type Safety**: 100% with strict validation
- **Larastan Integration**: Laravel-specific rules enabled
- **Carbon Extension**: Enabled for better DateTime handling

#### Developer Experience
- **Pre-commit Hook**: `composer precommit` (format + phpstan + tests in 2-3s)
- **Quality Checks**: `composer quality` and `composer quality-full`
- **Composer Scripts**: Enhanced with comprehensive quality commands
- **Mutation Annotations**: Strategic use of `@pest-mutate-ignore` for equivalent mutations

#### Code Enhancements
- **Type Safety**: Strict validation of config returns with `is_int()` and `is_bool()`
- **PHPDoc**: Enhanced with `@property` annotations for dynamic properties
- **Test Models**: Added proper type hints for PHPStan MAX compliance
- **Defensive Code**: BCMath fallback handling with proper annotations

#### Configuration
- **phpstan.neon**: Updated with bleeding edge and proper ignoreErrors
- **phpstan-baseline.neon**: Optimized to 9 errors (Pest false positives)
- **pint.json**: Enhanced formatting rules
- **phpunit.xml.dist**: Proper coverage and source configuration

### Technical Metrics
- PHPStan: Level 6 → MAX (9) + Bleeding Edge
- Tests: 22 → 129 (+486%)
- Assertions: 61 → 489 (+702%)
- Code Coverage: ~95% → 100.0%
- Mutation Score: 58.62% → 72.73% (+24%)
- Mutations Tested: 34 → 40 (+18%)

### Breaking Changes
- None - All changes are internal quality improvements

## 1.0.1 - 2025-10-11

### Modified
- **README.md**: Remove the notice about Packagist


