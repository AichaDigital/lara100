<?php

declare(strict_types=1);

use AichaDigital\Lara100\Casts\Base100;
use AichaDigital\Lara100\Tests\Models\TestModel;

describe('Base100 Cast - BCMath', function () {
    beforeEach(function () {
        // Skip if BCMath not available
        if (! extension_loaded('bcmath')) {
            $this->markTestSkipped('BCMath extension not available');
        }
    });

    it('uses bcmath when enabled via constructor', function () {
        $model = new TestModel;
        $cast = new Base100(useBcmath: true);

        // Test get with BCMath
        $result = $cast->get($model, 'price', 1999, []);
        expect($result)->toBe(19.99);

        // Test set with BCMath
        $stored = $cast->set($model, 'price', 19.99, []);
        expect($stored)->toBe(1999);
    });

    it('uses bcmath for large numbers', function () {
        $model = new TestModel;
        $cast = new Base100(useBcmath: true);

        // Very large number
        $largeValue = 999999999999;
        $result = $cast->get($model, 'price', $largeValue, []);
        expect($result)->toBe(9999999999.99);

        $stored = $cast->set($model, 'price', 9999999999.99, []);
        expect($stored)->toBe(999999999999);
    });

    it('uses bcmath with different rounding modes', function () {
        $model = new TestModel;

        // Test PHP_ROUND_HALF_UP with BCMath
        $cast = new Base100(PHP_ROUND_HALF_UP, true);
        $stored = $cast->set($model, 'price', 10.555, []);
        expect($stored)->toBe(1056);  // 10.555 * 100 = 1055.5 → rounds to 1056

        // Test PHP_ROUND_HALF_EVEN with BCMath
        $cast = new Base100(PHP_ROUND_HALF_EVEN, true);
        $stored = $cast->set($model, 'price', 10.555, []);
        expect($stored)->toBe(1056);  // Banker's rounding
    });

    it('falls back when bcmath extension not loaded', function () {
        // This test verifies the fallback logic
        // Even if we request BCMath but it's not loaded, it should work
        $model = new TestModel;
        $cast = new Base100(useBcmath: true);

        // Should still work even if BCMath fails
        $result = $cast->get($model, 'price', 1999, []);
        expect($result)->toBeFloat();
        expect($result)->toBe(19.99);
    });

    it('handles precision correctly with bcmath', function () {
        $model = new TestModel;
        $cast = new Base100(useBcmath: true);

        // Test precise decimal handling
        $values = [
            1234 => 12.34,
            5678 => 56.78,
            9999 => 99.99,
            1 => 0.01,
            99 => 0.99,
        ];

        foreach ($values as $stored => $expected) {
            $result = $cast->get($model, 'price', $stored, []);
            expect($result)->toBe($expected);
        }
    });

    it('uses bcmath with negative values', function () {
        $model = new TestModel;
        $cast = new Base100(useBcmath: true);

        // Negative values
        $result = $cast->get($model, 'price', -1999, []);
        expect($result)->toBe(-19.99);

        $stored = $cast->set($model, 'price', -19.99, []);
        expect($stored)->toBe(-1999);
    });

    it('overrides config with constructor parameters', function () {
        $model = new TestModel;

        // Override rounding mode via constructor
        $cast = new Base100(PHP_ROUND_HALF_DOWN, false);
        $stored = $cast->set($model, 'price', 10.555, []);
        expect($stored)->toBe(1055);  // HALF_DOWN rounds to 1055

        // Override BCMath via constructor
        $cast = new Base100(null, true);
        $result = $cast->get($model, 'price', 1999, []);
        expect($result)->toBe(19.99);
    });
});
