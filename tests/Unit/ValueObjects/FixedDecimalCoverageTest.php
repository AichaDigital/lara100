<?php

declare(strict_types=1);

use AichaDigital\Lara100\Exceptions\InvalidFixedDecimal;
use AichaDigital\Lara100\RoundingMode;
use AichaDigital\Lara100\ValueObjects\FixedDecimal;
use Brick\Math\BigDecimal;

// ---------------------------------------------------------------------------
// Negative-scale guards (lines 37, 63, 98, 113)
// ---------------------------------------------------------------------------

it('rejects a negative scale in ofDecimalString', function () {
    FixedDecimal::ofDecimalString('1.00', -1);
})->throws(InvalidFixedDecimal::class, 'Scale must be zero or positive, got -1.');

it('rejects a negative scale in ofFloat', function () {
    FixedDecimal::ofFloat(1.5, -2);
})->throws(InvalidFixedDecimal::class, 'Scale must be zero or positive, got -2.');

it('rejects a negative scale in dividedBy', function () {
    FixedDecimal::ofUnscaled(10, 0)->dividedBy(3, -1, RoundingMode::HalfUp);
})->throws(InvalidFixedDecimal::class, 'Scale must be zero or positive, got -1.');

it('rejects a negative scale in toScale', function () {
    FixedDecimal::ofUnscaled(100, 2)->toScale(-1);
})->throws(InvalidFixedDecimal::class, 'Scale must be zero or positive, got -1.');

// ---------------------------------------------------------------------------
// minus() (line 85)
// ---------------------------------------------------------------------------

it('subtracts two FixedDecimal values exactly', function () {
    $result = FixedDecimal::ofDecimalString('10.00')->minus(FixedDecimal::ofDecimalString('3.25'));

    expect($result->toDecimalString())->toBe('6.75')
        ->and($result->unscaledValue())->toBe(675);
});

// ---------------------------------------------------------------------------
// toFloat() escape hatch (line 189)
// ---------------------------------------------------------------------------

it('returns a native float from toFloat', function () {
    $d = FixedDecimal::ofUnscaled(1999, 2);

    expect($d->toFloat())->toBeFloat()
        ->and($d->toFloat())->toBe(19.99);
});

it('toFloat returns 0.0 for a zero value', function () {
    expect(FixedDecimal::zero(2)->toFloat())->toBe(0.0);
});

// ---------------------------------------------------------------------------
// toBigDecimal() escape hatch (lines 196-198)
// ---------------------------------------------------------------------------

it('returns the underlying BigDecimal from toBigDecimal', function () {
    $d = FixedDecimal::ofUnscaled(1999, 2);
    $bd = $d->toBigDecimal();

    expect($bd)->toBeInstanceOf(BigDecimal::class)
        ->and((string) $bd)->toBe('19.99')
        ->and($bd->getScale())->toBe(2);
});

// ---------------------------------------------------------------------------
// unscaledValue() overflow (lines 173-174) + InvalidFixedDecimal::unscaledOverflow (line 29)
// ---------------------------------------------------------------------------

it('throws InvalidFixedDecimal when unscaled value exceeds PHP_INT_MAX', function () {
    // Build a value whose unscaled integer overflows PHP_INT_MAX.
    // PHP_INT_MAX is 9223372036854775807 (64-bit).  Multiplying a large
    // FixedDecimal by 100 keeps the internal BigDecimal exact (brick/math
    // uses arbitrary-precision), but toInt() on the unscaled BigInteger
    // throws Brick\Math\Exception\IntegerOverflowException.
    $big = FixedDecimal::ofUnscaled(PHP_INT_MAX, 0)->multipliedBy(100);

    $big->unscaledValue();
})->throws(InvalidFixedDecimal::class, 'Unscaled value');

it('unscaledOverflow factory produces the expected message', function () {
    $e = InvalidFixedDecimal::unscaledOverflow('92233720368547758070');

    expect($e)->toBeInstanceOf(InvalidFixedDecimal::class)
        ->and($e->getMessage())->toContain('92233720368547758070')
        ->and($e->getMessage())->toContain('PHP integer range');
});
