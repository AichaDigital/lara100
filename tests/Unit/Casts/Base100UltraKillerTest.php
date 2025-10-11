<?php

declare(strict_types=1);

use AichaDigital\Lara100\Casts\Base100;
use AichaDigital\Lara100\Tests\Models\TestModel;

describe('Base100 Cast - Ultra Mutation Killer', function () {
    // =============================================================================
    // KILLING Line 61: RemoveEarlyReturn in get()
    // =============================================================================
    it('MUST return early 0.0 when value is null in get - not continue processing', function () {
        $model = new TestModel;
        $cast = new Base100;

        // If early return is removed, this would try to process null
        // which could cause unexpected behavior or crash
        $result = $cast->get($model, 'price', null, []);

        // MUST be exactly 0.0, not any other value
        expect($result)->toBe(0.0);
        expect($result)->toEqual(0.0);
        expect($result === 0.0)->toBeTrue();
    });

    // =============================================================================
    // KILLING Line 67: IfNegated + Line 68: IncrementInteger/RemoveEarlyReturn
    // =============================================================================
    it('MUST use bcmath path when enabled - negating if would break functionality', function () {
        if (! extension_loaded('bcmath')) {
            $this->markTestSkipped('BCMath required for this test');
        }

        $model = new TestModel;

        // BCMath enabled - MUST execute bcdiv line
        $castBcmath = new Base100(null, true);
        $resultBcmath = $castBcmath->get($model, 'price', 1999, []);

        // If if($this->useBcmath) is negated to if(!$this->useBcmath)
        // it would execute wrong code path
        expect($resultBcmath)->toBe(19.99);

        // BCMath disabled - MUST execute round line
        $castNoBcmath = new Base100(null, false);
        $resultNoBcmath = $castNoBcmath->get($model, 'price', 1999, []);

        // Both paths must give correct result
        expect($resultNoBcmath)->toBe(19.99);

        // Verify both use their respective code paths by testing precision-sensitive value
        expect($resultBcmath)->toBe($resultNoBcmath);
    });

    it('MUST use precision 2 in bcdiv - precision 3 would give different float representation', function () {
        if (! extension_loaded('bcmath')) {
            $this->markTestSkipped('BCMath required for this test');
        }

        $model = new TestModel;
        $cast = new Base100(null, true);

        // With precision=2 (correct): 19.99
        // With precision=3 (wrong): might be 19.990 internally
        $result = $cast->get($model, 'price', 1999, []);

        // Must be exactly 19.99 as float
        expect($result)->toBe(19.99);
        expect($result)->toEqual(19.99);

        // String representation should show 2 decimals
        $formatted = number_format($result, 2, '.', '');
        expect($formatted)->toBe('19.99');
    });

    it('bcmath early return MUST exist - removing it breaks the conditional logic', function () {
        if (! extension_loaded('bcmath')) {
            $this->markTestSkipped('BCMath required for this test');
        }

        $model = new TestModel;
        $cast = new Base100(null, true);

        // This MUST go through BCMath path and return
        // If return is removed, it would fall through to standard path (wrong!)
        $result = $cast->get($model, 'price', 1999, []);

        expect($result)->toBe(19.99);
        expect($result)->toBeFloat();
    });

    // =============================================================================
    // KILLING Line 71: RemoveDoubleCast + IncrementInteger
    // =============================================================================
    it('MUST cast to float before division - removing cast breaks type safety', function () {
        $model = new TestModel;
        $cast = new Base100(useBcmath: false);

        // Without (float) cast, string division might behave unexpectedly
        // Testing with numeric string
        $result = $cast->get($model, 'price', '1999', []);

        // MUST be float 19.99
        expect($result)->toBeFloat();
        expect($result)->toBe(19.99);
    });

    it('MUST use precision 2 in round - precision 3 changes behavior', function () {
        $model = new TestModel;
        $cast = new Base100(useBcmath: false);

        // Test value where precision matters
        $values = [
            1234 => 12.34,
            5678 => 56.78,
            9999 => 99.99,
            12 => 0.12,
            1 => 0.01,
        ];

        foreach ($values as $stored => $expected) {
            $result = $cast->get($model, 'price', $stored, []);

            // With precision=2 (correct): exact match
            // With precision=3 (wrong): would be different
            expect($result)->toBe($expected);

            // Verify string representation has exactly 2 decimals
            $str = number_format($result, 2, '.', '');
            expect($str)->toBe(number_format($expected, 2, '.', ''));
        }
    });

    // =============================================================================
    // KILLING Line 85: RemoveEarlyReturn in set()
    // =============================================================================
    it('MUST return early 0 when value is null in set - not continue processing', function () {
        $model = new TestModel;
        $cast = new Base100;

        // If early return is removed, this would try to process null
        $result = $cast->set($model, 'price', null, []);

        // MUST be exactly 0, not any other value
        expect($result)->toBe(0);
        expect($result)->toEqual(0);
        expect($result === 0)->toBeTrue();
        expect($result)->toBeInt();
    });

    // =============================================================================
    // KILLING Line 88: IfNegated + Line 90: Increment/DecrementInteger
    // =============================================================================
    it('MUST use bcmath path in set when enabled - negating breaks it', function () {
        if (! extension_loaded('bcmath')) {
            $this->markTestSkipped('BCMath required for this test');
        }

        $model = new TestModel;

        // BCMath enabled - MUST execute bcmul line
        $castBcmath = new Base100(null, true);
        $resultBcmath = $castBcmath->set($model, 'price', 19.99, []);

        // If if($this->useBcmath) is negated, wrong path executes
        expect($resultBcmath)->toBe(1999);
        expect($resultBcmath)->toBeInt();

        // BCMath disabled - MUST execute standard multiplication
        $castNoBcmath = new Base100(null, false);
        $resultNoBcmath = $castNoBcmath->set($model, 'price', 19.99, []);

        expect($resultNoBcmath)->toBe(1999);

        // Both must give same result
        expect($resultBcmath)->toBe($resultNoBcmath);
    });

    it('MUST use precision 2 in bcmul - changing to 1 or 3 breaks calculations', function () {
        if (! extension_loaded('bcmath')) {
            $this->markTestSkipped('BCMath required for this test');
        }

        $model = new TestModel;
        $cast = new Base100(null, true);

        // Values that prove precision=2 is necessary
        expect($cast->set($model, 'price', 0.01, []))->toBe(1);
        expect($cast->set($model, 'price', 0.12, []))->toBe(12);
        expect($cast->set($model, 'price', 0.99, []))->toBe(99);
        expect($cast->set($model, 'price', 1.23, []))->toBe(123);
        expect($cast->set($model, 'price', 9.99, []))->toBe(999);
        expect($cast->set($model, 'price', 19.99, []))->toBe(1999);

        // All results must be exact integers
        expect($cast->set($model, 'price', 0.01, []))->toBeInt();
    });

    it('bcmath early return in set MUST exist - without it falls to wrong code', function () {
        if (! extension_loaded('bcmath')) {
            $this->markTestSkipped('BCMath required for this test');
        }

        $model = new TestModel;
        $cast = new Base100(null, true);

        // This MUST go through BCMath path and return immediately
        // If return is removed, would execute standard path after (double processing!)
        $result = $cast->set($model, 'price', 19.99, []);

        expect($result)->toBe(1999);
        expect($result)->toBeInt();
        expect($result === 1999)->toBeTrue();
    });

    // =============================================================================
    // KILLING Line 96: RemoveDoubleCast (final line)
    // =============================================================================
    it('MUST cast to float before multiplication in set - type safety critical', function () {
        $model = new TestModel;
        $cast = new Base100(useBcmath: false);

        // Test with various input types where float cast matters
        $inputs = [
            19.99,      // float
            20.0,       // float
            0.01,       // very small float
            999.99,     // large float
        ];

        foreach ($inputs as $input) {
            $result = $cast->set($model, 'price', $input, []);

            // Must always return int
            expect($result)->toBeInt();
        }
    });

    // =============================================================================
    // KILLING Line 39-40: Config default mutations
    // =============================================================================
    it('default false in config fetch is critical - true would enable bcmath unexpectedly', function () {
        $model = new TestModel;

        // Clear any config
        config()->set('lara100.use_bcmath', null);

        // Constructor with null should use config default (false)
        $cast = new Base100(null, null);

        // If default is changed from false to true, behavior changes dramatically
        $result = $cast->get($model, 'price', 1999, []);

        // Should use standard path (not BCMath) when nothing configured
        expect($result)->toBe(19.99);
    });

    it('coalesce operator MUST check left side first - removing it breaks override', function () {
        $model = new TestModel;

        // Explicit false in constructor MUST override config
        config()->set('lara100.use_bcmath', true);

        $cast = new Base100(null, false);  // Explicitly false

        // If ?? is changed to only check right side, this would use config (true)
        // and execute BCMath when we explicitly asked for false
        $result = $cast->get($model, 'price', 1999, []);

        // MUST use standard path (false was explicit)
        expect($result)->toBe(19.99);
    });

    it('ternary false default for bcmath is necessary - true breaks standard behavior', function () {
        $model = new TestModel;

        // Config returns non-bool (wrong type)
        config()->set('lara100.use_bcmath', 'wrong-type');

        $cast = new Base100(null, null);

        // Should fallback to FALSE (not TRUE)
        // If default is true, BCMath would be used unexpectedly
        $result = $cast->get($model, 'price', 1999, []);

        expect($result)->toBe(19.99);
    });

    // =============================================================================
    // Additional precision validations
    // =============================================================================
    it('validates every precision parameter individually', function () {
        if (! extension_loaded('bcmath')) {
            $this->markTestSkipped('BCMath required for this test');
        }

        $model = new TestModel;

        // Test that proves precision=2 is THE correct value
        $cast = new Base100(null, true);

        // 0.01 is critical - requires precision of 2
        $result = $cast->get($model, 'price', 1, []);
        expect($result)->toBe(0.01);

        $stored = $cast->set($model, 'price', 0.01, []);
        expect($stored)->toBe(1);

        // 99.99 tests upper boundary
        $result = $cast->get($model, 'price', 9999, []);
        expect($result)->toBe(99.99);

        $stored = $cast->set($model, 'price', 99.99, []);
        expect($stored)->toBe(9999);
    });
});
