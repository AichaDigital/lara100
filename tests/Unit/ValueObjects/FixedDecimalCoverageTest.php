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

// ---------------------------------------------------------------------------
// Mutant 1 — negative-scale guards: scale=0 must NOT throw
// Kills SmallerToSmallerOrEqual (<→<=) and IncrementInteger (0→1)
// ---------------------------------------------------------------------------

it('ofDecimalString with scale=0 succeeds and keeps the value as integer', function () {
    $d = FixedDecimal::ofDecimalString('19', 0);

    expect($d->toDecimalString())->toBe('19')
        ->and($d->scale())->toBe(0);
});

it('ofFloat with scale=0 succeeds and produces an integer-scale decimal', function () {
    $d = FixedDecimal::ofFloat(19.0, 0);

    expect($d->toDecimalString())->toBe('19')
        ->and($d->scale())->toBe(0);
});

it('ofFloat with scale=-1 throws InvalidFixedDecimal (kills DecrementInteger / boundary)', function () {
    FixedDecimal::ofFloat(1.5, -1);
})->throws(InvalidFixedDecimal::class, 'Scale must be zero or positive, got -1.');

it('dividedBy with scale=0 succeeds and returns an integer-scale result', function () {
    $result = FixedDecimal::ofUnscaled(100, 0)->dividedBy(2, 0, RoundingMode::HalfUp);

    expect($result->toDecimalString())->toBe('50')
        ->and($result->scale())->toBe(0);
});

// ---------------------------------------------------------------------------
// Mutant 2 — dividedBy: InstanceOfToFalse on `$divisor instanceof self`
// Kills by dividing with a FixedDecimal divisor; if instanceof is mutated to
// false, $divisor->value (BigDecimal) is passed raw and brick throws or differs.
// ---------------------------------------------------------------------------

it('dividedBy accepts a FixedDecimal divisor and produces the correct quotient', function () {
    $result = FixedDecimal::ofUnscaled(1000, 2)
        ->dividedBy(FixedDecimal::ofUnscaled(400, 2), 2, RoundingMode::HalfUp);

    expect($result->toDecimalString())->toBe('2.50')
        ->and($result->scale())->toBe(2);
});

// ---------------------------------------------------------------------------
// Mutant 4 — FixedDecimalCast::get() RemoveIntegerCast on (int) $value
// Kills by passing a numeric string; without (int) cast, ofUnscaled('1999', …)
// fails a strict_types TypeError and the success assertion never runs.
// ---------------------------------------------------------------------------

it('FixedDecimalCast get() with a numeric-string database value returns the correct FixedDecimal', function () {
    $cast = new AichaDigital\Lara100\Casts\FixedDecimalCast(2);
    $model = new AichaDigital\Lara100\Tests\Models\FixedDecimalTestModel;

    $result = $cast->get($model, 'price', '1999', []);

    expect($result)->toBeInstanceOf(FixedDecimal::class);
    assert($result instanceof FixedDecimal);
    expect($result->toDecimalString())->toBe('19.99')
        ->and($result->scale())->toBe(2);
});

// ---------------------------------------------------------------------------
// Mutant 5 — InvalidFixedDecimal::nonFixedDecimalAssignment ConcatSwitchSides + ConcatRemoveRight
// toStartWith kills ConcatSwitchSides; toContain the second part kills ConcatRemoveRight.
// ---------------------------------------------------------------------------

it('nonFixedDecimalAssignment message starts with FixedDecimalCast accepts only FixedDecimal', function () {
    $e = InvalidFixedDecimal::nonFixedDecimalAssignment(19);

    expect($e->getMessage())
        ->toStartWith('FixedDecimalCast accepts only FixedDecimal')
        ->and($e->getMessage())->toContain('Build it explicitly via FixedDecimal::ofUnscaled()');
});

// ---------------------------------------------------------------------------
// Mutant 6 — InvalidFixedDecimal::fromEngine (line 24)
// ConcatRemoveLeft, ConcatRemoveRight, ConcatSwitchSides: assert both parts present.
// DecrementInteger / IncrementInteger on the `0` (code param): assert code is 0.
// ---------------------------------------------------------------------------

it('fromEngine wraps the previous exception message with the expected prefix', function () {
    $previous = new RuntimeException('division by zero');
    $e = InvalidFixedDecimal::fromEngine($previous);

    expect($e->getMessage())
        ->toStartWith('Invalid decimal value: ')
        ->and($e->getMessage())->toContain('division by zero')
        ->and($e->getCode())->toBe(0)
        ->and($e->getPrevious())->toBe($previous);
});
