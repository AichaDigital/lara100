<?php

declare(strict_types=1);

use AichaDigital\Lara100\Casts\FixedDecimalCast;
use AichaDigital\Lara100\Exceptions\InvalidFixedDecimal;
use AichaDigital\Lara100\Tests\Models\FixedDecimalTestModel;
use AichaDigital\Lara100\ValueObjects\FixedDecimal;

it('stores the unscaled integer and reads back a FixedDecimal at the declared scale', function () {
    $m = FixedDecimalTestModel::create([
        'fd_amount' => FixedDecimal::ofDecimalString('19.99'),
        'fd_rate' => FixedDecimal::ofDecimalString('21.0000'),
    ]);

    expect($m->getRawOriginal('fd_amount'))->toBe(1999)
        ->and($m->getRawOriginal('fd_rate'))->toBe(210000);

    $fresh = FixedDecimalTestModel::firstOrFail();

    $fdAmount = $fresh->fd_amount;
    assert($fdAmount instanceof FixedDecimal);
    $fdRate = $fresh->fd_rate;
    assert($fdRate instanceof FixedDecimal);

    expect($fresh->fd_amount)->toBeInstanceOf(FixedDecimal::class)
        ->and($fdAmount->toDecimalString())->toBe('19.99')
        ->and($fdAmount->scale())->toBe(2)
        ->and($fdRate->toDecimalString())->toBe('21.0000')
        ->and($fdRate->scale())->toBe(4);
});

it('preserves null on nullable columns', function () {
    $m = FixedDecimalTestModel::create(['fd_amount' => null]);

    expect($m->fd_amount)->toBeNull()
        ->and($m->getRawOriginal('fd_amount'))->toBeNull();
});

it('rounds to the declared scale on assignment', function () {
    $m = new FixedDecimalTestModel;
    $m->fd_amount = FixedDecimal::ofDecimalString('19.999'); // scale 3 -> column scale 2

    expect($m->getAttributes()['fd_amount'])->toBe(2000); // 20.00 unscaled, HalfUp
});

it('rejects a raw int assignment', function () {
    $m = new FixedDecimalTestModel;
    $m->fd_amount = 1999; // @phpstan-ignore assign.propertyType (intentional: strict cast must reject scalar assignment)
})->throws(InvalidFixedDecimal::class);

it('rejects a numeric-string assignment', function () {
    $m = new FixedDecimalTestModel;
    $m->fd_amount = '19.99'; // @phpstan-ignore assign.propertyType (intentional: strict cast must reject scalar assignment)
})->throws(InvalidFixedDecimal::class);

it('rejects a float assignment', function () {
    $m = new FixedDecimalTestModel;
    $m->fd_amount = 19.99; // @phpstan-ignore assign.propertyType (intentional: strict cast must reject scalar assignment)
})->throws(InvalidFixedDecimal::class);

it('throws InvalidFixedDecimal when the column contains a non-numeric value', function () {
    $cast = new FixedDecimalCast(2);
    $cast->get(new FixedDecimalTestModel, 'fd_amount', 'corrupt', []);
})->throws(InvalidFixedDecimal::class);
