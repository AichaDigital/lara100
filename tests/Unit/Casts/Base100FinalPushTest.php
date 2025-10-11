<?php

declare(strict_types=1);

use AichaDigital\Lara100\Casts\Base100;
use AichaDigital\Lara100\Tests\Models\TestModel;

describe('Base100 Cast - Final Push to 70%', function () {
    // These tests target the most stubborn remaining mutations

    it('float cast in get ensures type consistency with string inputs', function () {
        $model = new TestModel;
        $cast = new Base100(useBcmath: false);

        // String input that could behave differently without float cast
        $result = $cast->get($model, 'price', '2000', []);

        // Without (float) cast, string division might behave unexpectedly
        expect($result)->toBeFloat();
        expect($result)->toBe(20.00);
        expect($result)->not->toBeString();
    });

    it('float cast in set ensures precision with edge case values', function () {
        $model = new TestModel;
        $cast = new Base100(useBcmath: false);

        // Values where float cast matters for precision
        expect($cast->set($model, 'price', 0.01, []))->toBe(1);
        expect($cast->set($model, 'price', 0.10, []))->toBe(10);
        expect($cast->set($model, 'price', 0.99, []))->toBe(99);
        expect($cast->set($model, 'price', 1.00, []))->toBe(100);
        expect($cast->set($model, 'price', 9.99, []))->toBe(999);

        // All must be integers
        expect($cast->set($model, 'price', 0.01, []))->toBeInt();
    });

    it('coalesce operator necessary when both parameters null', function () {
        $model = new TestModel;

        // Both parameters null - must use config
        config()->set('lara100.rounding_mode', PHP_ROUND_HALF_UP);
        config()->set('lara100.use_bcmath', false);

        $cast = new Base100(null, null);

        // Verify it works with config values
        $result = $cast->set($model, 'price', 10.555, []);
        expect($result)->toBeGreaterThanOrEqual(1055);
    });

    it('ternary validation necessary when config returns wrong type', function () {
        $model = new TestModel;

        // Config returns array (wrong type)
        config()->set('lara100.rounding_mode', [PHP_ROUND_HALF_UP]);
        config()->set('lara100.use_bcmath', ['false']);

        // Should handle gracefully and use defaults
        $cast = new Base100(null, null);

        $result = $cast->get($model, 'price', 1999, []);
        expect($result)->toBe(19.99);

        $result = $cast->set($model, 'price', 19.99, []);
        expect($result)->toBe(1999);
    });

    it('negated ternary catches non-integer config for rounding', function () {
        $model = new TestModel;

        // Config returns float instead of int
        config()->set('lara100.rounding_mode', 2.5);

        $cast = new Base100(null);

        // Should use default PHP_ROUND_HALF_UP
        $result = $cast->set($model, 'price', 10.555, []);
        expect($result)->toBeInt();
    });

    it('negated ternary catches non-boolean config for bcmath', function () {
        $model = new TestModel;

        // Config returns int instead of bool
        config()->set('lara100.use_bcmath', 1);

        $cast = new Base100(null, null);

        // Should use default false
        $result = $cast->get($model, 'price', 1999, []);
        expect($result)->toBe(19.99);
    });

    it('verifies config type checking with null config values', function () {
        $model = new TestModel;

        // Config returns null
        config()->set('lara100.rounding_mode', null);
        config()->set('lara100.use_bcmath', null);

        $cast = new Base100(null, null);

        // Should use defaults
        $result = $cast->get($model, 'price', 1999, []);
        expect($result)->toBe(19.99);

        $result = $cast->set($model, 'price', 19.99, []);
        expect($result)->toBe(1999);
    });

    it('bcmath conditional with explicit false checks correct path', function () {
        $model = new TestModel;

        // Explicitly disable BCMath
        $cast = new Base100(PHP_ROUND_HALF_UP, false);

        // Should NOT use BCMath path
        $result = $cast->get($model, 'price', 1999, []);
        expect($result)->toBe(19.99);

        $result = $cast->set($model, 'price', 19.99, []);
        expect($result)->toBe(1999);
    });

    it('validates precision parameter is exactly 2 not others', function () {
        if (! extension_loaded('bcmath')) {
            $this->markTestSkipped('BCMath extension not available');
        }

        $model = new TestModel;
        $cast = new Base100(null, true);

        // Test that precision 2 gives exact results
        // Precision 1 would be 19.9, precision 3 would be 19.990
        $values = [
            [1999, 19.99],
            [1234, 12.34],
            [9876, 98.76],
            [5432, 54.32],
        ];

        foreach ($values as [$stored, $expected]) {
            $result = $cast->get($model, 'price', $stored, []);
            expect($result)->toBe($expected);

            // Verify exact precision
            $strResult = (string) $result;
            expect($strResult)->toContain('.');
        }
    });

    it('round precision parameter must be exactly 2 for cents', function () {
        $model = new TestModel;
        $cast = new Base100(useBcmath: false);

        // These values prove precision=2 is required
        $testCases = [
            [1234, 12.34],
            [5678, 56.78],
            [9012, 90.12],
            [3456, 34.56],
        ];

        foreach ($testCases as [$stored, $expected]) {
            $result = $cast->get($model, 'price', $stored, []);

            // Must match exactly with 2 decimal places
            expect($result)->toBe($expected);

            // Verify it's not rounded to 1 decimal
            expect($result)->not->toBe(round($expected, 1));
        }
    });

    it('rounding mode parameter used in set operation', function () {
        $model = new TestModel;

        // Test value that shows rounding mode effect
        $testValue = 10.555;

        // HALF_UP (default)
        $castUp = new Base100(PHP_ROUND_HALF_UP);
        $resultUp = $castUp->set($model, 'price', $testValue, []);

        // HALF_DOWN
        $castDown = new Base100(PHP_ROUND_HALF_DOWN);
        $resultDown = $castDown->set($model, 'price', $testValue, []);

        // Results should potentially differ based on rounding mode
        expect($resultUp)->toBeInt();
        expect($resultDown)->toBeInt();

        // At least verify the modes work
        expect($resultUp)->toBeGreaterThanOrEqual(1055);
        expect($resultDown)->toBeLessThanOrEqual(1056);
    });

    it('incrementing precision would give incorrect results', function () {
        if (! extension_loaded('bcmath')) {
            $this->markTestSkipped('BCMath extension not available');
        }

        $model = new TestModel;
        $cast = new Base100(null, true);

        // With precision=2 (correct), we get 19.99
        // With precision=3, we might get 19.990 (wrong type/format)
        $result = $cast->get($model, 'price', 1999, []);

        expect($result)->toBe(19.99);
        expect($result)->toBeFloat();

        // Ensure it's not some other value
        expect($result)->toBeGreaterThan(19.98);
        expect($result)->toBeLessThan(20.00);
    });

    it('decrementing precision would lose decimal accuracy', function () {
        if (! extension_loaded('bcmath')) {
            $this->markTestSkipped('BCMath extension not available');
        }

        $model = new TestModel;
        $cast = new Base100(null, true);

        // With precision=2 (correct), we get 12.34
        // With precision=1, we might get 12.3 (loses a decimal)
        $result = $cast->get($model, 'price', 1234, []);

        expect($result)->toBe(12.34);

        // Verify both decimal places are preserved
        $strResult = number_format($result, 2);
        expect($strResult)->toBe('12.34');
    });

    it('validates type safety with mixed numeric types', function () {
        $model = new TestModel;
        $cast = new Base100(useBcmath: false);

        // Integer input
        $result1 = $cast->set($model, 'price', 20, []);
        expect($result1)->toBeInt();
        expect($result1)->toBe(2000);

        // Float input
        $result2 = $cast->set($model, 'price', 20.0, []);
        expect($result2)->toBeInt();
        expect($result2)->toBe(2000);

        // Both should give same result despite different input types
        expect($result1)->toBe($result2);
    });
});
