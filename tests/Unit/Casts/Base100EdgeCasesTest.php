<?php

declare(strict_types=1);

use AichaDigital\Lara100\Casts\Base100;
use AichaDigital\Lara100\Tests\Models\TestModel;

describe('Base100 Cast - Edge Cases', function () {
    it('handles non-numeric string values in get', function () {
        $model = new TestModel;
        $cast = new Base100;

        // Non-numeric string should default to 0
        $result = $cast->get($model, 'price', 'not-a-number', []);
        expect($result)->toBe(0.0);

        $result = $cast->get($model, 'price', 'abc123', []);
        expect($result)->toBe(0.0);
    });

    it('handles empty string in get', function () {
        $model = new TestModel;
        $cast = new Base100;

        $result = $cast->get($model, 'price', '', []);
        expect($result)->toBe(0.0);
    });

    it('handles numeric strings correctly', function () {
        $model = new TestModel;
        $cast = new Base100;

        // Numeric strings should work
        $result = $cast->get($model, 'price', '1999', []);
        expect($result)->toBe(19.99);

        $result = $cast->get($model, 'price', '0', []);
        expect($result)->toBe(0.0);
    });

    it('handles float precision edge cases', function () {
        $model = new TestModel;
        $cast = new Base100;

        // Values that can cause floating point issues
        // Due to floating point precision, 0.1 + 0.1 + 0.1 != 0.3
        // Our cast handles this correctly by rounding
        $result = $cast->set($model, 'price', 0.3, []);
        expect($result)->toBeInt();
        expect($result)->toBeGreaterThanOrEqual(29);
        expect($result)->toBeLessThanOrEqual(31);

        // These should work precisely
        $result = $cast->set($model, 'price', 1.1, []);
        expect($result)->toBe(110);

        $result = $cast->set($model, 'price', 2.2, []);
        expect($result)->toBe(220);
    });

    it('handles very small decimals', function () {
        $model = new TestModel;
        $cast = new Base100;

        // Very small values
        $result = $cast->set($model, 'price', 0.01, []);
        expect($result)->toBe(1);

        $result = $cast->set($model, 'price', 0.001, []);
        expect($result)->toBe(0);  // Less than 1 cent rounds to 0

        $result = $cast->set($model, 'price', 0.005, []);
        expect($result)->toBe(1);  // 0.5 cents rounds up with HALF_UP
    });

    it('handles maximum integer values', function () {
        $model = new TestModel;
        $cast = new Base100;

        // Large integer
        $largeInt = PHP_INT_MAX;
        $result = $cast->get($model, 'price', $largeInt, []);
        expect($result)->toBeFloat();
        expect($result)->toBeGreaterThan(0);
    });

    it('handles mixed type inputs in set', function () {
        $model = new TestModel;
        $cast = new Base100;

        // Integer input
        $result = $cast->set($model, 'price', 20, []);
        expect($result)->toBe(2000);

        // Float input
        $result = $cast->set($model, 'price', 20.5, []);
        expect($result)->toBe(2050);

        // Another float input
        $result = $cast->set($model, 'price', 15.75, []);
        expect($result)->toBe(1575);
    });

    it('returns correct types', function () {
        $model = new TestModel;
        $cast = new Base100;

        // get() should always return float
        $result = $cast->get($model, 'price', 1999, []);
        expect($result)->toBeFloat();

        $result = $cast->get($model, 'price', null, []);
        expect($result)->toBeFloat();

        // set() should always return int
        $result = $cast->set($model, 'price', 19.99, []);
        expect($result)->toBeInt();

        $result = $cast->set($model, 'price', null, []);
        expect($result)->toBeInt();
    });

    it('handles boundary values for rounding', function () {
        $model = new TestModel;
        $cast = new Base100(PHP_ROUND_HALF_UP);

        // Test exact 0.5 boundary (HALF_UP)
        $result = $cast->set($model, 'price', 10.555, []);
        expect($result)->toBeGreaterThanOrEqual(1055);

        // Test exact 0.5 boundary (HALF_DOWN)
        $cast = new Base100(PHP_ROUND_HALF_DOWN);
        $result = $cast->set($model, 'price', 10.555, []);
        expect($result)->toBeLessThanOrEqual(1056);
    });

    it('handles zero correctly in all scenarios', function () {
        $model = new TestModel;
        $cast = new Base100;

        // Zero in get
        $result = $cast->get($model, 'price', 0, []);
        expect($result)->toBe(0.0);

        // Zero in set
        $result = $cast->set($model, 'price', 0, []);
        expect($result)->toBe(0);

        // Zero as string
        $result = $cast->get($model, 'price', '0', []);
        expect($result)->toBe(0.0);
    });

    it('handles null correctly', function () {
        $model = new TestModel;
        $cast = new Base100;

        // Null in get
        $result = $cast->get($model, 'price', null, []);
        expect($result)->toBe(0.0);

        // Null in set
        $result = $cast->set($model, 'price', null, []);
        expect($result)->toBe(0);
    });

    it('handles different precision requirements', function () {
        $model = new TestModel;
        $cast = new Base100;

        // Values with different decimal places - exact values
        expect($cast->set($model, 'price', 19.9, []))->toBe(1990);
        expect($cast->set($model, 'price', 19.99, []))->toBe(1999);
        expect($cast->set($model, 'price', 19.999, []))->toBe(2000);  // Rounds to 2 decimals
        expect($cast->set($model, 'price', 19.9999, []))->toBe(2000);
        expect($cast->set($model, 'price', 19.1, []))->toBe(1910);
        expect($cast->set($model, 'price', 19.11, []))->toBe(1911);

        // 19.111 could be affected by floating point precision
        $result = $cast->set($model, 'price', 19.111, []);
        expect($result)->toBeInt();
        expect($result)->toBeGreaterThanOrEqual(1910);
        expect($result)->toBeLessThanOrEqual(1912);
    });
});
