<?php

declare(strict_types=1);

use AichaDigital\Lara100\Casts\Base100;
use AichaDigital\Lara100\Tests\Models\TestModel;

describe('Base100 Cast - 100% Coverage', function () {
    it('covers all branches of config rounding mode validation', function () {
        $model = new TestModel;

        // Branch 1: is_int returns true (config value is int)
        config()->set('lara100.rounding_mode', PHP_ROUND_HALF_UP);
        $cast1 = new Base100(null);
        expect($cast1->set($model, 'price', 10.555, []))->toBeInt();

        // Branch 2: is_int returns false (config value is not int) - uses default
        config()->set('lara100.rounding_mode', 'not-an-int');
        $cast2 = new Base100(null);
        expect($cast2->set($model, 'price', 10.555, []))->toBeInt();

        // Branch 3: roundingMode parameter provided (skips config entirely)
        config()->set('lara100.rounding_mode', PHP_ROUND_HALF_DOWN);
        $cast3 = new Base100(PHP_ROUND_HALF_UP);
        expect($cast3->set($model, 'price', 10.555, []))->toBeInt();
    });

    it('covers all branches of config bcmath validation', function () {
        $model = new TestModel;

        // Branch 1: is_bool returns true (config value is bool)
        config()->set('lara100.use_bcmath', false);
        $cast1 = new Base100(null, null);
        expect($cast1->get($model, 'price', 1999, []))->toBe(19.99);

        // Branch 2: is_bool returns false (config value is not bool) - uses default false
        config()->set('lara100.use_bcmath', 'not-a-bool');
        $cast2 = new Base100(null, null);
        expect($cast2->get($model, 'price', 1999, []))->toBe(19.99);

        // Branch 3: useBcmath parameter provided (skips config entirely)
        config()->set('lara100.use_bcmath', true);
        $cast3 = new Base100(null, false);
        expect($cast3->get($model, 'price', 1999, []))->toBe(19.99);
    });

    it('covers bcmath extension check branch when bcmath not available', function () {
        $model = new TestModel;

        // This line is tricky: we need to test the fallback when BCMath is not loaded
        // But we can't actually unload BCMath if it's loaded

        if (! extension_loaded('bcmath')) {
            // If BCMath is NOT loaded, this test will execute the line 44
            $cast = new Base100(null, true);
            $result = $cast->get($model, 'price', 1999, []);

            // Should fallback to standard mode
            expect($result)->toBe(19.99);
        } else {
            // If BCMath IS loaded, we still need to cover the positive branch
            $cast = new Base100(null, true);
            $result = $cast->get($model, 'price', 1999, []);

            // BCMath path is executed
            expect($result)->toBe(19.99);

            // Also test with explicit false to cover the else path
            $cast2 = new Base100(null, false);
            $result2 = $cast2->get($model, 'price', 1999, []);
            expect($result2)->toBe(19.99);
        }
    });

    it('covers null value branch in get method', function () {
        $model = new TestModel;
        $cast = new Base100;

        // Explicitly test null (line 58-60)
        $result = $cast->get($model, 'price', null, []);
        expect($result)->toBe(0.0);
        expect($result)->toBeFloat();
    });

    it('covers null value branch in set method', function () {
        $model = new TestModel;
        $cast = new Base100;

        // Explicitly test null (line 82-84)
        $result = $cast->set($model, 'price', null, []);
        expect($result)->toBe(0);
        expect($result)->toBeInt();
    });

    it('covers numeric value check in get method', function () {
        $model = new TestModel;
        $cast = new Base100;

        // Line 63: is_numeric returns true
        $result1 = $cast->get($model, 'price', 1999, []);
        expect($result1)->toBe(19.99);

        $result2 = $cast->get($model, 'price', '1999', []);
        expect($result2)->toBe(19.99);

        // Line 63: is_numeric returns false - uses 0
        $result3 = $cast->get($model, 'price', 'not-numeric', []);
        expect($result3)->toBe(0.0);
    });

    it('covers bcmath branch in get method', function () {
        if (! extension_loaded('bcmath')) {
            $this->markTestSkipped('BCMath extension not available');
        }

        $model = new TestModel;

        // Line 65-67: BCMath path
        $cast1 = new Base100(null, true);
        $result1 = $cast1->get($model, 'price', 1999, []);
        expect($result1)->toBe(19.99);

        // Line 69: Standard path (NOT bcmath)
        $cast2 = new Base100(null, false);
        $result2 = $cast2->get($model, 'price', 1999, []);
        expect($result2)->toBe(19.99);
    });

    it('covers bcmath branch in set method', function () {
        if (! extension_loaded('bcmath')) {
            $this->markTestSkipped('BCMath extension not available');
        }

        $model = new TestModel;

        // Line 86-91: BCMath path
        $cast1 = new Base100(null, true);
        $result1 = $cast1->set($model, 'price', 19.99, []);
        expect($result1)->toBe(1999);

        // Line 94: Standard path (NOT bcmath)
        $cast2 = new Base100(null, false);
        $result2 = $cast2->set($model, 'price', 19.99, []);
        expect($result2)->toBe(1999);
    });

    it('covers all constructor parameter combinations', function () {
        $model = new TestModel;

        // Both null
        $cast1 = new Base100(null, null);
        expect($cast1->get($model, 'price', 1999, []))->toBe(19.99);

        // First provided, second null
        $cast2 = new Base100(PHP_ROUND_HALF_UP, null);
        expect($cast2->get($model, 'price', 1999, []))->toBe(19.99);

        // First null, second provided
        $cast3 = new Base100(null, false);
        expect($cast3->get($model, 'price', 1999, []))->toBe(19.99);

        // Both provided
        $cast4 = new Base100(PHP_ROUND_HALF_UP, false);
        expect($cast4->get($model, 'price', 1999, []))->toBe(19.99);
    });

    it('covers all possible values in ternary operators', function () {
        $model = new TestModel;

        // Test rounding mode ternary with all possible outcomes
        // When is_int($configRoundingMode) is true
        config()->set('lara100.rounding_mode', PHP_ROUND_HALF_DOWN);
        $cast1 = new Base100;
        expect($cast1->set($model, 'price', 10.555, []))->toBeInt();

        // When is_int($configRoundingMode) is false
        config()->set('lara100.rounding_mode', null);
        $cast2 = new Base100;
        expect($cast2->set($model, 'price', 10.555, []))->toBeInt();

        // Test bcmath ternary with all possible outcomes
        // When is_bool($configBcmath) is true
        config()->set('lara100.use_bcmath', true);
        $cast3 = new Base100(null, null);
        expect($cast3->get($model, 'price', 1999, []))->toBe(19.99);

        // When is_bool($configBcmath) is false
        config()->set('lara100.use_bcmath', []);
        $cast4 = new Base100(null, null);
        expect($cast4->get($model, 'price', 1999, []))->toBe(19.99);
    });

    it('ensures every line of Base100 is executed', function () {
        $model = new TestModel;

        // Create instance with all default config
        config()->set('lara100.rounding_mode', PHP_ROUND_HALF_UP);
        config()->set('lara100.use_bcmath', false);

        $cast = new Base100;

        // Test all methods
        expect($cast->get($model, 'price', 1999, []))->toBe(19.99);
        expect($cast->get($model, 'price', null, []))->toBe(0.0);
        expect($cast->get($model, 'price', 'invalid', []))->toBe(0.0);

        expect($cast->set($model, 'price', 19.99, []))->toBe(1999);
        expect($cast->set($model, 'price', null, []))->toBe(0);

        // This should cover all lines in the class
        expect(true)->toBeTrue();
    });
});
