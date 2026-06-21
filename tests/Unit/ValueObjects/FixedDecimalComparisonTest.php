<?php

declare(strict_types=1);

use AichaDigital\Lara100\ValueObjects\FixedDecimal;

it('compares values regardless of scale', function () {
    $a = FixedDecimal::ofDecimalString('0.3');     // scale 1
    $b = FixedDecimal::ofDecimalString('0.30');    // scale 2

    expect($a->isEqualTo($b))->toBeTrue()
        ->and($a->compareTo($b))->toBe(0);
});

it('orders values', function () {
    $small = FixedDecimal::ofDecimalString('1.00');
    $big = FixedDecimal::ofDecimalString('2.00');

    expect($small->isLessThan($big))->toBeTrue()
        ->and($big->isGreaterThan($small))->toBeTrue()
        ->and($small->compareTo($big))->toBe(-1)
        ->and($big->compareTo($small))->toBe(1);
});

it('reports sign and zero', function () {
    expect(FixedDecimal::zero(2)->isZero())->toBeTrue()
        ->and(FixedDecimal::ofDecimalString('1.00')->isPositive())->toBeTrue()
        ->and(FixedDecimal::ofDecimalString('-1.00')->isNegative())->toBeTrue();
});

it('keeps compareTo coherent with the boolean helpers', function () {
    $a = FixedDecimal::ofDecimalString('5.00');
    $b = FixedDecimal::ofDecimalString('7.00');

    expect($a->compareTo($b) < 0)->toBe($a->isLessThan($b))
        ->and($a->compareTo($a) === 0)->toBe($a->isEqualTo($a));
});
