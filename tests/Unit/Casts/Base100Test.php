<?php

declare(strict_types=1);

use AichaDigital\Lara100\Casts\Base100;
use AichaDigital\Lara100\Tests\Models\TestModel;

describe('Base100 Cast', function () {
    it('converts decimal to base-100 integer on get', function () {
        $cast  = new Base100;
        $model = new TestModel;

        expect($cast->get($model, 'price', 123.45, []))->toBe(12345)
            ->and($cast->get($model, 'price', 1.00, []))->toBe(100)
            ->and($cast->get($model, 'price', 0.01, []))->toBe(1)
            ->and($cast->get($model, 'price', 19.99, []))->toBe(1999);
    });

    it('converts base-100 integer to decimal on set', function () {
        $cast  = new Base100;
        $model = new TestModel;

        expect($cast->set($model, 'price', 12345, []))->toBe(123.45)
            ->and($cast->set($model, 'price', 100, []))->toBe(1.00)
            ->and($cast->set($model, 'price', 1, []))->toBe(0.01)
            ->and($cast->set($model, 'price', 1999, []))->toBe(19.99);
    });

    it('handles zero correctly', function () {
        $cast  = new Base100;
        $model = new TestModel;

        expect($cast->get($model, 'price', 0.00, []))->toBe(0)
            ->and($cast->set($model, 'price', 0, []))->toBe(0.00);
    });

    it('handles null values', function () {
        $cast  = new Base100;
        $model = new TestModel;

        expect($cast->get($model, 'price', null, []))->toBe(0)
            ->and($cast->set($model, 'price', null, []))->toBe(0.00);
    });

    it('handles negative values', function () {
        $cast  = new Base100;
        $model = new TestModel;

        expect($cast->get($model, 'price', -50.25, []))->toBe(-5025)
            ->and($cast->set($model, 'price', -5025, []))->toBe(-50.25);
    });

    it('rounds appropriately', function () {
        $cast  = new Base100;
        $model = new TestModel;

        // Testing rounding on get (DB → Model)
        expect($cast->get($model, 'price', 10.555, []))->toBe(1056) // rounds to 10.56 * 100
            ->and($cast->get($model, 'price', 10.554, []))->toBe(1055); // rounds to 10.55 * 100

        // Testing rounding on set (Model → DB)
        expect($cast->set($model, 'price', 1055, []))->toBe(10.55)
            ->and($cast->set($model, 'price', 1056, []))->toBe(10.56);
    });

    it('returns correct types', function () {
        $cast  = new Base100;
        $model = new TestModel;

        $getValue = $cast->get($model, 'price', 100.00, []);
        $setValue = $cast->set($model, 'price', 10000, []);

        expect($getValue)->toBeInt()
            ->and($setValue)->toBeFloat();
    });

    it('handles large numbers', function () {
        $cast  = new Base100;
        $model = new TestModel;

        expect($cast->get($model, 'price', 999999.99, []))->toBe(99999999)
            ->and($cast->set($model, 'price', 99999999, []))->toBe(999999.99);
    });
});
