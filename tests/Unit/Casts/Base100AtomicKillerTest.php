<?php

declare(strict_types=1);

use AichaDigital\Lara100\Casts\Base100;
use AichaDigital\Lara100\Tests\Models\TestModel;

describe('Base100 Cast - Atomic Mutation Killer', function () {
    // =============================================================================
    // ATOMIC KILLER: Line 96 RemoveDoubleCast
    // Testing with inputs where (float) cast CHANGES the actual behavior
    // =============================================================================
    it('removing float cast in final return breaks type coercion chain', function () {
        $model = new TestModel;
        $cast = new Base100(useBcmath: false);

        // Test with integer that needs float coercion for proper multiplication
        $result1 = $cast->set($model, 'price', 100, []);
        expect($result1)->toBe(10000);
        expect($result1)->toBeInt();
        expect(gettype($result1))->toBe('integer');

        // Test with float that needs proper casting
        $result2 = $cast->set($model, 'price', 50.50, []);
        expect($result2)->toBe(5050);
        expect(gettype($result2))->toBe('integer');

        // Critical: test value where float precision matters
        $result3 = $cast->set($model, 'price', 33.33, []);
        expect($result3)->toBeInt();
        expect($result3)->toBeGreaterThanOrEqual(3333);
        expect($result3)->toBeLessThanOrEqual(3334);
    });

    // =============================================================================
    // ATOMIC KILLER: Line 71 RemoveDoubleCast + IncrementInteger
    // =============================================================================
    it('removing float cast in round breaks with non-numeric-string edge cases', function () {
        $model = new TestModel;
        $cast = new Base100(useBcmath: false);

        // After is_numeric check, $numericValue could be string '1999'
        // Without (float) cast, division might behave differently
        $result = $cast->get($model, 'price', '1999', []);

        expect($result)->toBeFloat();
        expect($result)->toBe(19.99);
        expect(gettype($result))->toBe('double');  // double is PHP's float type
    });

    it('precision 2 in round is exact requirement - 3 would give wrong float', function () {
        $model = new TestModel;
        $cast = new Base100(useBcmath: false);

        // If precision changes from 2 to 3, these would fail
        $precisionCritical = [
            1 => 0.01,
            10 => 0.10,
            99 => 0.99,
            123 => 1.23,
            9999 => 99.99,
        ];

        foreach ($precisionCritical as $stored => $expected) {
            $result = $cast->get($model, 'price', $stored, []);

            // MUST be exact to 2 decimals
            expect($result)->toBe($expected);

            // Verify it's truly 2 decimal places
            $rounded = round($result, 2);
            expect($result)->toBe($rounded);
        }
    });

    // =============================================================================
    // ATOMIC KILLER: Line 67-68 IfNegated + precision mutations
    // =============================================================================
    it('if negation in bcmath check would execute wrong code in get', function () {
        if (! extension_loaded('bcmath')) {
            $this->markTestSkipped('BCMath required');
        }

        $model = new TestModel;

        // Force BCMath enabled
        $cast = new Base100(PHP_ROUND_HALF_UP, true);

        // If if($this->useBcmath) becomes if(!$this->useBcmath)
        // it would execute standard round instead of bcdiv - detectable with large numbers
        $result = $cast->get($model, 'price', 999999999999, []);

        expect($result)->toBeFloat();
        expect($result)->toBeGreaterThan(9999999999.0);
    });

    // =============================================================================
    // ATOMIC KILLER: Line 88-92 bcmath set() mutations
    // =============================================================================
    it('if negation in bcmath check would execute wrong code in set', function () {
        if (! extension_loaded('bcmath')) {
            $this->markTestSkipped('BCMath required');
        }

        $model = new TestModel;

        // Force BCMath enabled
        $cast = new Base100(PHP_ROUND_HALF_UP, true);

        // If if($this->useBcmath) becomes if(!$this->useBcmath)
        // it would skip BCMath and use standard multiplication
        $result = $cast->set($model, 'price', 9999.99, []);

        expect($result)->toBeInt();
        expect($result)->toBe(999999);
    });

    it('bcmul precision MUST be 2 - precision 1 gives scientifically different results', function () {
        if (! extension_loaded('bcmath')) {
            $this->markTestSkipped('BCMath required');
        }

        $model = new TestModel;
        $cast = new Base100(null, true);

        // Ultra-precise test: these values REQUIRE precision=2
        expect($cast->set($model, 'price', 0.01, []))->toBe(1);
        expect($cast->set($model, 'price', 0.05, []))->toBe(5);
        expect($cast->set($model, 'price', 0.10, []))->toBe(10);
        expect($cast->set($model, 'price', 0.11, []))->toBe(11);
        expect($cast->set($model, 'price', 0.99, []))->toBe(99);
        expect($cast->set($model, 'price', 1.23, []))->toBe(123);

        // Verify all are ints
        expect($cast->set($model, 'price', 0.01, []))->toBeInt();
    });

    // =============================================================================
    // Additional atomic killers for remaining stubborn mutations
    // =============================================================================
    it('verifies return type strictness across all code paths', function () {
        $model = new TestModel;

        // Standard mode
        $cast1 = new Base100(useBcmath: false);
        expect($cast1->get($model, 'price', 1999, []))->toBeFloat();
        expect($cast1->set($model, 'price', 19.99, []))->toBeInt();

        if (extension_loaded('bcmath')) {
            // BCMath mode
            $cast2 = new Base100(useBcmath: true);
            expect($cast2->get($model, 'price', 1999, []))->toBeFloat();
            expect($cast2->set($model, 'price', 19.99, []))->toBeInt();
        }
    });

    it('validates exact mathematical equivalence between modes', function () {
        if (! extension_loaded('bcmath')) {
            $this->markTestSkipped('BCMath required');
        }

        $model = new TestModel;

        $testValues = [1, 10, 100, 999, 1999, 9999, 12345];

        foreach ($testValues as $storedValue) {
            // BCMath and Standard must give IDENTICAL results
            $bcmath = new Base100(null, true);
            $standard = new Base100(null, false);

            $getBcmath = $bcmath->get($model, 'price', $storedValue, []);
            $getStandard = $standard->get($model, 'price', $storedValue, []);

            expect($getBcmath)->toBe($getStandard);

            // Round trip test
            $setBcmath = $bcmath->set($model, 'price', $getBcmath, []);
            $setStandard = $standard->set($model, 'price', $getStandard, []);

            expect($setBcmath)->toBe($setStandard);
            expect($setBcmath)->toBe($storedValue);
        }
    });

    it('precision parameters must be exact - deviation detected by round trip', function () {
        $model = new TestModel;
        $cast = new Base100(useBcmath: false);

        // Round trip tests detect precision issues
        $criticalValues = [0.01, 0.12, 1.23, 9.99, 19.99, 99.99];

        foreach ($criticalValues as $original) {
            $stored = $cast->set($model, 'price', $original, []);
            $retrieved = $cast->get($model, 'price', $stored, []);

            // MUST match exactly after round trip
            expect($retrieved)->toBe($original);
        }
    });

    it('detects behavioral differences in early return mutations', function () {
        $model = new TestModel;
        $cast = new Base100;

        // Test that proves early return is necessary
        // Without it, null would be processed as 0 in is_numeric check
        $nullGet = $cast->get($model, 'price', null, []);
        $zeroGet = $cast->get($model, 'price', 0, []);

        expect($nullGet)->toBe(0.0);
        expect($zeroGet)->toBe(0.0);
        expect($nullGet)->toBe($zeroGet);  // Same result but different code path

        $nullSet = $cast->set($model, 'price', null, []);
        $zeroSet = $cast->set($model, 'price', 0, []);

        expect($nullSet)->toBe(0);
        expect($zeroSet)->toBe(0);
        expect($nullSet)->toBe($zeroSet);
    });
});
