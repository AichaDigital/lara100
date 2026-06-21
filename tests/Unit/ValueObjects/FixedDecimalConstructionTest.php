<?php

declare(strict_types=1);

use AichaDigital\Lara100\Exceptions\InvalidFixedDecimal;
use AichaDigital\Lara100\RoundingMode;
use AichaDigital\Lara100\ValueObjects\FixedDecimal;

it('builds from unscaled value and scale', function () {
    $d = FixedDecimal::ofUnscaled(1999, 2);

    expect($d->scale())->toBe(2)
        ->and($d->unscaledValue())->toBe(1999)
        ->and($d->toDecimalString())->toBe('19.99');
});

it('builds from a decimal string preserving its natural scale', function () {
    expect(FixedDecimal::ofDecimalString('21.0000')->scale())->toBe(4)
        ->and(FixedDecimal::ofDecimalString('19.99')->scale())->toBe(2);
});

it('re-scales a decimal string to the requested scale with HalfUp', function () {
    expect(FixedDecimal::ofDecimalString('19.995', 2)->toDecimalString())->toBe('20.00');
});

it('rejects a negative scale', function () {
    FixedDecimal::ofUnscaled(1, -1);
})->throws(InvalidFixedDecimal::class);

it('rejects a malformed decimal string as a lara100 exception', function () {
    FixedDecimal::ofDecimalString('not-a-number');
})->throws(InvalidFixedDecimal::class);

it('rejects NAN and INF floats', function () {
    FixedDecimal::ofFloat(NAN, 2);
})->throws(InvalidFixedDecimal::class);

it('builds from floats with every exposed rounding mode', function () {
    foreach (RoundingMode::cases() as $mode) {
        expect(FixedDecimal::ofFloat(1.25, 1, $mode))->toBeInstanceOf(FixedDecimal::class);
    }
});

it('builds zero at a given scale', function () {
    expect(FixedDecimal::zero(2)->toDecimalString())->toBe('0.00');
});

it('serializes to an exact decimal string', function () {
    expect(json_encode(FixedDecimal::ofUnscaled(1999, 2)))->toBe('"19.99"');
});

it('keeps high-scale unscaled values exact', function () {
    expect(FixedDecimal::ofUnscaled(210000, 4)->toDecimalString())->toBe('21.0000');
});
