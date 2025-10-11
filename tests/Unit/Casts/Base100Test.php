<?php

declare(strict_types=1);

use AichaDigital\Lara100\Casts\Base100;
use AichaDigital\Lara100\Tests\Models\TestModel;

describe('Base100 Cast', function () {
    it('converts integer cents to decimal on get (DB → App)', function () {
        $cast = new Base100;
        $model = new TestModel;

        expect($cast->get($model, 'price', 12345, []))->toBe(123.45)
            ->and($cast->get($model, 'price', 1999, []))->toBe(19.99)
            ->and($cast->get($model, 'price', 100, []))->toBe(1.00)
            ->and($cast->get($model, 'price', 1, []))->toBe(0.01);
    });

    it('converts decimal to integer cents on set (App → DB)', function () {
        $cast = new Base100;
        $model = new TestModel;

        expect($cast->set($model, 'price', 123.45, []))->toBe(12345)
            ->and($cast->set($model, 'price', 19.99, []))->toBe(1999)
            ->and($cast->set($model, 'price', 1.00, []))->toBe(100)
            ->and($cast->set($model, 'price', 0.01, []))->toBe(1);
    });

    it('handles zero correctly', function () {
        $cast = new Base100;
        $model = new TestModel;

        expect($cast->get($model, 'price', 0, []))->toBe(0.00)
            ->and($cast->set($model, 'price', 0.00, []))->toBe(0);
    });

    it('handles null values', function () {
        $cast = new Base100;
        $model = new TestModel;

        expect($cast->get($model, 'price', null, []))->toBe(0.00)
            ->and($cast->set($model, 'price', null, []))->toBe(0);
    });

    it('handles negative values', function () {
        $cast = new Base100;
        $model = new TestModel;

        expect($cast->get($model, 'price', -5025, []))->toBe(-50.25)
            ->and($cast->set($model, 'price', -50.25, []))->toBe(-5025);
    });

    it('rounds appropriately', function () {
        $cast = new Base100;
        $model = new TestModel;

        // Testing rounding on get (DB → Model): 1056 cents / 100 = 10.56
        expect($cast->get($model, 'price', 1056, []))->toBe(10.56)
            ->and($cast->get($model, 'price', 1055, []))->toBe(10.55);

        // Testing rounding on set (Model → DB): 10.555 * 100 = 1056 (rounds)
        expect($cast->set($model, 'price', 10.555, []))->toBe(1056)
            ->and($cast->set($model, 'price', 10.554, []))->toBe(1055);
    });

    it('returns correct types', function () {
        $cast = new Base100;
        $model = new TestModel;

        $getValue = $cast->get($model, 'price', 10000, []);  // 10000 cents → 100.00
        $setValue = $cast->set($model, 'price', 100.00, []); // 100.00 → 10000 cents

        expect($getValue)->toBeFloat()
            ->and($setValue)->toBeInt();
    });

    it('handles large numbers', function () {
        $cast = new Base100;
        $model = new TestModel;

        expect($cast->get($model, 'price', 99999999, []))->toBe(999999.99)
            ->and($cast->set($model, 'price', 999999.99, []))->toBe(99999999);
    });
});
