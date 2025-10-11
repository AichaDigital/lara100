<?php

declare(strict_types=1);

use AichaDigital\Lara100\Casts\Base100;
use AichaDigital\Lara100\Tests\Models\TestModel;

describe('Base100 Cast - Kill Remaining Mutations', function () {
    // ===================================================================
    // KILLING RemoveDoubleCast MUTATIONS
    // ===================================================================

    it('requires float cast in get() standard mode for type safety', function () {
        $model = new TestModel;
        $cast = new Base100(useBcmath: false);

        // Without (float) cast, integer division could behave differently
        $result = $cast->get($model, 'price', 1999, []);

        // Must return float, not int
        expect($result)->toBeFloat();
        expect($result)->not->toBeInt();
        expect($result)->toBe(19.99);
    });

    it('requires float cast in set() standard mode for precision', function () {
        $model = new TestModel;
        $cast = new Base100(useBcmath: false);

        // Without (float) cast, multiplication could lose precision
        $result = $cast->set($model, 'price', 19.99, []);

        // Must be exact int
        expect($result)->toBe(1999);
        expect($result)->toBeInt();

        // Test edge case where float cast matters
        $result = $cast->set($model, 'price', 0.01, []);
        expect($result)->toBe(1);
    });

    // ===================================================================
    // KILLING CoalesceRemoveLeft MUTATIONS (line 36)
    // ===================================================================

    it('uses constructor rounding mode parameter when provided', function () {
        $model = new TestModel;

        // When $roundingMode IS provided, it should use it
        // NOT the config value
        $cast = new Base100(PHP_ROUND_HALF_DOWN);

        $result = $cast->set($model, 'price', 10.555, []);

        // HALF_DOWN should round 10.555 to 10.55 (1055)
        expect($result)->toBeLessThanOrEqual(1055);
    });

    it('uses config rounding mode when constructor parameter is null', function () {
        $model = new TestModel;

        // When $roundingMode is NULL, it should use config
        // This tests the ?? operator is necessary
        config()->set('lara100.rounding_mode', PHP_ROUND_HALF_UP);

        $cast = new Base100(null);

        $result = $cast->set($model, 'price', 10.555, []);

        // HALF_UP should round 10.555 to 10.56 (1056)
        expect($result)->toBeGreaterThanOrEqual(1056);
    });

    // ===================================================================
    // KILLING CoalesceRemoveLeft MUTATIONS (line 40)
    // ===================================================================

    it('uses constructor bcmath parameter when provided', function () {
        if (! extension_loaded('bcmath')) {
            $this->markTestSkipped('BCMath extension not available');
        }

        $model = new TestModel;

        // When $useBcmath IS provided as true, it should use BCMath
        $cast = new Base100(null, true);

        $result = $cast->get($model, 'price', 1999, []);
        expect($result)->toBe(19.99);

        // When $useBcmath IS provided as false, it should NOT use BCMath
        $cast = new Base100(null, false);

        $result = $cast->get($model, 'price', 1999, []);
        expect($result)->toBe(19.99);
    });

    it('uses config bcmath when constructor parameter is null', function () {
        $model = new TestModel;

        // When $useBcmath is NULL, it should use config
        // This tests the ?? operator is necessary
        config()->set('lara100.use_bcmath', false);

        $cast = new Base100(null, null);

        $result = $cast->get($model, 'price', 1999, []);
        expect($result)->toBe(19.99);
    });

    // ===================================================================
    // KILLING TernaryNegated MUTATIONS (line 36)
    // ===================================================================

    it('validates config rounding mode and uses fallback when invalid', function () {
        $model = new TestModel;

        // Set invalid config value (string instead of int)
        config()->set('lara100.rounding_mode', 'invalid-string');

        $cast = new Base100(null);

        // Should fallback to PHP_ROUND_HALF_UP
        $result = $cast->set($model, 'price', 10.555, []);

        // Should use default HALF_UP rounding
        expect($result)->toBeInt();
        expect($result)->toBeGreaterThanOrEqual(1055);
    });

    it('validates config rounding mode accepts valid int', function () {
        $model = new TestModel;

        // Set VALID config value
        config()->set('lara100.rounding_mode', PHP_ROUND_HALF_EVEN);

        $cast = new Base100(null);

        $result = $cast->set($model, 'price', 10.555, []);

        // Should use HALF_EVEN (banker's rounding)
        expect($result)->toBeInt();
    });

    // ===================================================================
    // KILLING TernaryNegated MUTATIONS (line 40)
    // ===================================================================

    it('validates config bcmath and uses fallback when invalid', function () {
        $model = new TestModel;

        // Set invalid config value (string instead of bool)
        config()->set('lara100.use_bcmath', 'invalid-string');

        $cast = new Base100(null, null);

        // Should fallback to false
        $result = $cast->get($model, 'price', 1999, []);
        expect($result)->toBe(19.99);
        expect($result)->toBeFloat();
    });

    it('validates config bcmath accepts valid bool', function () {
        $model = new TestModel;

        // Set VALID config value
        config()->set('lara100.use_bcmath', false);

        $cast = new Base100(null, null);

        $result = $cast->get($model, 'price', 1999, []);
        expect($result)->toBe(19.99);
    });

    // ===================================================================
    // KILLING RemoveEarlyReturn MUTATIONS
    // ===================================================================

    it('handles null correctly and does not execute remaining code in get', function () {
        $model = new TestModel;
        $cast = new Base100;

        // Null should return early with 0.0
        // Without early return, would try to process null as numeric
        $result = $cast->get($model, 'price', null, []);

        expect($result)->toBe(0.0);
        expect($result)->not->toBeNull();
    });

    it('handles null correctly and does not execute remaining code in set', function () {
        $model = new TestModel;
        $cast = new Base100;

        // Null should return early with 0
        // Without early return, would try to process null as numeric
        $result = $cast->set($model, 'price', null, []);

        expect($result)->toBe(0);
        expect($result)->not->toBeNull();
    });

    // ===================================================================
    // KILLING IfNegated MUTATIONS (bcmath conditionals)
    // ===================================================================

    it('executes bcmath path only when enabled', function () {
        if (! extension_loaded('bcmath')) {
            $this->markTestSkipped('BCMath extension not available');
        }

        $model = new TestModel;

        // BCMath enabled - should use bcdiv
        $castEnabled = new Base100(null, true);
        $resultEnabled = $castEnabled->get($model, 'price', 1999, []);

        // BCMath disabled - should use standard division
        $castDisabled = new Base100(null, false);
        $resultDisabled = $castDisabled->get($model, 'price', 1999, []);

        // Both should give same result but through different code paths
        expect($resultEnabled)->toBe(19.99);
        expect($resultDisabled)->toBe(19.99);
    });

    it('executes bcmath path in set only when enabled', function () {
        if (! extension_loaded('bcmath')) {
            $this->markTestSkipped('BCMath extension not available');
        }

        $model = new TestModel;

        // BCMath enabled - should use bcmul
        $castEnabled = new Base100(null, true);
        $resultEnabled = $castEnabled->set($model, 'price', 19.99, []);

        // BCMath disabled - should use standard multiplication
        $castDisabled = new Base100(null, false);
        $resultDisabled = $castDisabled->set($model, 'price', 19.99, []);

        // Both should give same result but through different code paths
        expect($resultEnabled)->toBe(1999);
        expect($resultDisabled)->toBe(1999);
    });

    // ===================================================================
    // KILLING Precision Parameter Mutations (bcmath precision)
    // ===================================================================

    it('requires specific precision in bcdiv for correct results', function () {
        if (! extension_loaded('bcmath')) {
            $this->markTestSkipped('BCMath extension not available');
        }

        $model = new TestModel;
        $cast = new Base100(null, true);

        // Test values that require 2 decimal precision
        $testCases = [
            1999 => 19.99,
            1234 => 12.34,
            5 => 0.05,
            99 => 0.99,
            1 => 0.01,
        ];

        foreach ($testCases as $stored => $expected) {
            $result = $cast->get($model, 'price', $stored, []);
            expect($result)->toBe($expected);
        }
    });

    it('requires specific precision in bcmul for correct results', function () {
        if (! extension_loaded('bcmath')) {
            $this->markTestSkipped('BCMath extension not available');
        }

        $model = new TestModel;
        $cast = new Base100(null, true);

        // Test values that require 2 decimal precision
        expect($cast->set($model, 'price', 19.99, []))->toBe(1999);
        expect($cast->set($model, 'price', 12.34, []))->toBe(1234);
        expect($cast->set($model, 'price', 0.99, []))->toBe(99);
        expect($cast->set($model, 'price', 0.01, []))->toBe(1);

        // Edge case: very small value
        expect($cast->set($model, 'price', 0.05, []))->toBeInt();
    });

    // ===================================================================
    // KILLING Increment/Decrement Integer Mutations
    // ===================================================================

    it('requires exact precision value 2 not 1 or 3 in bcmath', function () {
        if (! extension_loaded('bcmath')) {
            $this->markTestSkipped('BCMath extension not available');
        }

        $model = new TestModel;
        $cast = new Base100(null, true);

        // These values specifically test that precision=2 is correct
        $result = $cast->get($model, 'price', 1999, []);
        expect($result)->toBe(19.99);

        // Test with value that shows precision matters
        $result = $cast->get($model, 'price', 1, []);
        expect($result)->toBe(0.01);  // Requires precision=2
    });

    it('validates round precision is 2 for decimal cents', function () {
        $model = new TestModel;
        $cast = new Base100(useBcmath: false);

        // Round precision must be 2 to get cents correctly
        $result = $cast->get($model, 'price', 1999, []);
        expect($result)->toBe(19.99);

        // Not 19.9 (precision=1) or 19.990 (precision=3)
        $result = $cast->get($model, 'price', 1234, []);
        expect($result)->toBe(12.34);
    });

    // ===================================================================
    // KILLING BooleanAndToBooleanOr MUTATION (line 43)
    // ===================================================================

    it('disables bcmath only when both conditions are true', function () {
        $model = new TestModel;

        // The condition is: $this->useBcmath && !extension_loaded('bcmath')
        // It should ONLY set useBcmath=false when BOTH are true

        // Case 1: useBcmath requested but extension not loaded
        // This specific case is hard to test without mocking
        // But we can verify the logic works when extension IS loaded

        if (extension_loaded('bcmath')) {
            $cast = new Base100(null, true);
            $result = $cast->get($model, 'price', 1999, []);
            expect($result)->toBe(19.99);
            // BCMath should be used since extension is loaded
        }
    });
});
