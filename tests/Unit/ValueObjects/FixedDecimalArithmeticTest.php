<?php

declare(strict_types=1);

use AichaDigital\Lara100\Exceptions\InvalidFixedDecimal;
use AichaDigital\Lara100\RoundingMode;
use AichaDigital\Lara100\ValueObjects\FixedDecimal;

it('adds 0.1 + 0.2 exactly (the float drift the package promises to kill)', function () {
    $sum = FixedDecimal::ofDecimalString('0.1')->plus(FixedDecimal::ofDecimalString('0.2'));

    expect($sum->toDecimalString())->toBe('0.3');
});

it('sums 100x 19.99 with no accumulation drift', function () {
    $total = FixedDecimal::zero(2);

    foreach (range(1, 100) as $ignored) {
        $total = $total->plus(FixedDecimal::ofDecimalString('19.99'));
    }

    expect($total->toDecimalString())->toBe('1999.00')
        ->and($total->unscaledValue())->toBe(199900);
});

it('keeps add/subtract at the max operand scale', function () {
    $r = FixedDecimal::ofDecimalString('1.5')->plus(FixedDecimal::ofDecimalString('2.25'));

    expect($r->scale())->toBe(2)->and($r->toDecimalString())->toBe('3.75');
});

it('sums scales on multiply', function () {
    $r = FixedDecimal::ofDecimalString('1.50')->multipliedBy(FixedDecimal::ofDecimalString('2.00'));

    expect($r->scale())->toBe(4)->and($r->toDecimalString())->toBe('3.0000');
});

it('multiplies by an int factor', function () {
    expect(FixedDecimal::ofDecimalString('19.99')->multipliedBy(3)->toDecimalString())->toBe('59.97');
});

it('divides with an explicit scale and rounding mode', function () {
    expect(FixedDecimal::ofUnscaled(1, 0)->dividedBy(3, 6, RoundingMode::HalfUp)->toDecimalString())
        ->toBe('0.333333');
});

it('rescales with each rounding mode', function () {
    expect(FixedDecimal::ofDecimalString('19.995')->toScale(2, RoundingMode::HalfUp)->toDecimalString())->toBe('20.00')
        ->and(FixedDecimal::ofDecimalString('19.995')->toScale(2, RoundingMode::HalfDown)->toDecimalString())->toBe('19.99')
        ->and(FixedDecimal::ofDecimalString('2.125')->toScale(2, RoundingMode::HalfEven)->toDecimalString())->toBe('2.12')
        ->and(FixedDecimal::ofDecimalString('1.5')->toScale(0, RoundingMode::Floor)->toDecimalString())->toBe('1')
        ->and(FixedDecimal::ofDecimalString('1.1')->toScale(0, RoundingMode::Ceiling)->toDecimalString())->toBe('2');
});

it('negates and takes absolute value (refund support)', function () {
    $refund = FixedDecimal::ofDecimalString('25.00')->negated();

    expect($refund->toDecimalString())->toBe('-25.00')
        ->and($refund->unscaledValue())->toBe(-2500)
        ->and($refund->abs()->toDecimalString())->toBe('25.00');
});

it('does not mutate the original instance', function () {
    $a = FixedDecimal::ofDecimalString('10.00');
    $b = $a->plus(FixedDecimal::ofDecimalString('5.00'));

    expect($a->toDecimalString())->toBe('10.00')
        ->and($b->toDecimalString())->toBe('15.00');
});

it('wraps a division-by-zero as a lara100 exception', function () {
    FixedDecimal::ofUnscaled(1, 0)->dividedBy(0, 2, RoundingMode::HalfUp);
})->throws(InvalidFixedDecimal::class);
