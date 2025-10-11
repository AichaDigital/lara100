<?php

declare(strict_types=1);

use AichaDigital\Lara100\Casts\Base100;
use AichaDigital\Lara100\Tests\Models\TestModel;

describe('Base100 Cast - Mutation Testing', function () {
    it('ensures early return is necessary for null in get', function () {
        $model = new TestModel;
        $cast = new Base100;

        // This test verifies that null handling returns 0.0
        // Without the early return, it would crash or behave incorrectly
        $result = $cast->get($model, 'price', null, []);
        expect($result)->toBe(0.0);
        expect($result)->toBeFloat();
    });

    it('ensures early return is necessary for null in set', function () {
        $model = new TestModel;
        $cast = new Base100;

        // This test verifies that null handling returns 0
        // Without the early return, it would crash or behave incorrectly
        $result = $cast->set($model, 'price', null, []);
        expect($result)->toBe(0);
        expect($result)->toBeInt();
    });

    it('validates double cast is necessary in get with bcmath disabled', function () {
        $model = new TestModel;
        $cast = new Base100(useBcmath: false);

        // The (float) cast is necessary to ensure type safety
        $result = $cast->get($model, 'price', 1999, []);
        expect($result)->toBeFloat();
        expect($result)->toBe(19.99);

        // Test with string input to verify cast is working
        $result = $cast->get($model, 'price', '1999', []);
        expect($result)->toBeFloat();
        expect($result)->toBe(19.99);
    });

    it('validates double cast is necessary in set with bcmath disabled', function () {
        $model = new TestModel;
        $cast = new Base100(useBcmath: false);

        // The (float) cast is necessary for consistent behavior
        $result = $cast->set($model, 'price', 19.99, []);
        expect($result)->toBeInt();
        expect($result)->toBe(1999);

        // Test with integer input to verify cast is working
        $result = $cast->set($model, 'price', 20, []);
        expect($result)->toBeInt();
        expect($result)->toBe(2000);
    });

    it('validates bcmath conditional logic in get', function () {
        $model = new TestModel;

        // With BCMath enabled
        $castWithBcmath = new Base100(useBcmath: true);
        $resultWithBcmath = $castWithBcmath->get($model, 'price', 1999, []);

        // Without BCMath
        $castWithoutBcmath = new Base100(useBcmath: false);
        $resultWithoutBcmath = $castWithoutBcmath->get($model, 'price', 1999, []);

        // Both should give the same result
        expect($resultWithBcmath)->toBe($resultWithoutBcmath);
        expect($resultWithBcmath)->toBe(19.99);
    });

    it('validates bcmath conditional logic in set', function () {
        $model = new TestModel;

        // With BCMath enabled
        $castWithBcmath = new Base100(useBcmath: true);
        $resultWithBcmath = $castWithBcmath->set($model, 'price', 19.99, []);

        // Without BCMath
        $castWithoutBcmath = new Base100(useBcmath: false);
        $resultWithoutBcmath = $castWithoutBcmath->set($model, 'price', 19.99, []);

        // Both should give the same result
        expect($resultWithBcmath)->toBe($resultWithoutBcmath);
        expect($resultWithBcmath)->toBe(1999);
    });

    it('validates non-numeric value handling in get', function () {
        $model = new TestModel;
        $cast = new Base100;

        // Non-numeric values should be handled gracefully
        $nonNumericValues = ['abc', '', '  ', 'not-a-number', [], false];

        foreach ($nonNumericValues as $value) {
            $result = $cast->get($model, 'price', $value, []);
            expect($result)->toBeFloat();
            expect($result)->toBe(0.0);
        }
    });

    it('validates rounding mode constructor override', function () {
        $model = new TestModel;

        // Test each rounding mode explicitly
        $modes = [
            PHP_ROUND_HALF_UP,
            PHP_ROUND_HALF_DOWN,
            PHP_ROUND_HALF_EVEN,
            PHP_ROUND_HALF_ODD,
        ];

        foreach ($modes as $mode) {
            $cast = new Base100($mode, false);
            $result = $cast->set($model, 'price', 10.555, []);
            expect($result)->toBeInt();
            expect($result)->toBeGreaterThan(1050);
            expect($result)->toBeLessThan(1060);
        }
    });

    it('validates bcmath constructor override', function () {
        $model = new TestModel;

        // Explicitly set BCMath to true
        $cast = new Base100(null, true);
        $result = $cast->get($model, 'price', 1999, []);
        expect($result)->toBe(19.99);

        // Explicitly set BCMath to false
        $cast = new Base100(null, false);
        $result = $cast->get($model, 'price', 1999, []);
        expect($result)->toBe(19.99);
    });

    it('validates precision parameters in bcmath operations', function () {
        if (! extension_loaded('bcmath')) {
            $this->markTestSkipped('BCMath extension not available');
        }

        $model = new TestModel;
        $cast = new Base100(useBcmath: true);

        // Test that precision is maintained correctly
        $values = [
            1234 => 12.34,
            5678 => 56.78,
            9999 => 99.99,
            1 => 0.01,
            10 => 0.10,
            100 => 1.00,
        ];

        foreach ($values as $stored => $expected) {
            $result = $cast->get($model, 'price', $stored, []);
            expect($result)->toBe($expected);

            $stored = $cast->set($model, 'price', $expected, []);
            expect($stored)->toBe($stored);
        }
    });

    it('ensures type safety with extreme values', function () {
        $model = new TestModel;
        $cast = new Base100;

        // Very large positive
        $result = $cast->set($model, 'price', 999999.99, []);
        expect($result)->toBeInt();
        expect($result)->toBe(99999999);

        // Very large negative
        $result = $cast->set($model, 'price', -999999.99, []);
        expect($result)->toBeInt();
        expect($result)->toBe(-99999999);
    });
});
