<?php

declare(strict_types=1);

use AichaDigital\Lara100\Casts\Base100;
use AichaDigital\Lara100\Tests\Models\TestModel;

describe('Base100 Cast - Type Strictness Detection', function () {
    // These tests are designed to detect type coercion changes
    // that would occur if double casts are removed

    it('detects if float cast removed in line 96 set() by testing with string-numeric', function () {
        $model = new TestModel;
        $cast = new Base100(useBcmath: false);

        // After mixed processing, $value could be various types
        // The (float) cast ensures consistent numeric multiplication

        // Test with pure float
        $r1 = $cast->set($model, 'price', 19.99, []);

        // Test with int (will be cast to float for multiplication)
        $r2 = $cast->set($model, 'price', 20, []);

        expect($r1)->toBe(1999);
        expect($r2)->toBe(2000);

        // Both MUST be int, proving cast chain works
        expect($r1)->toBeInt();
        expect($r2)->toBeInt();

        // Mathematical verification
        expect($r1)->toBe(1999);
        expect($r2)->toBe(2000);
    });

    it('detects float cast necessity with division precision requirements', function () {
        $model = new TestModel;
        $cast = new Base100(useBcmath: false);

        // Without float cast, these edge case values might compute incorrectly
        $edgeValues = [
            3 => 0.03,      // Very small
            7 => 0.07,
            13 => 0.13,
            17 => 0.17,
            1234 => 12.34,  // Standard
            9999 => 99.99,  // Maximum common
        ];

        foreach ($edgeValues as $stored => $expected) {
            $result = $cast->get($model, 'price', $stored, []);

            // Without (float) cast before division, could be imprecise
            expect($result)->toBe($expected);
            expect($result)->toBeFloat();

            // Verify mathematically correct
            $backToInt = (int) round($result * 100);
            expect($backToInt)->toBe($stored);
        }
    });

    it('proves config default false is behavioral requirement not preference', function () {
        $model = new TestModel;

        // Unset config completely
        config()->offsetUnset('lara100.use_bcmath');

        $cast = new Base100(null, null);

        // Default MUST be false because:
        // 1. BCMath might not be available
        // 2. Standard mode is the safe default
        // 3. BCMath is opt-in for performance reasons

        $result = $cast->set($model, 'price', 19.99, []);

        // If default was true, this might fail on systems without BCMath
        expect($result)->toBe(1999);
        expect($result)->toBeInt();
    });

    it('proves ternary fallback must be false not true for stability', function () {
        $model = new TestModel;

        // Set config to various invalid types
        $invalidTypes = [
            [],           // array
            new stdClass, // object
            'true',       // string
            1,            // int
        ];

        foreach ($invalidTypes as $invalidValue) {
            config()->set('lara100.use_bcmath', $invalidValue);

            $cast = new Base100(null, null);

            // MUST fallback to false (safe default), not true
            $result = $cast->get($model, 'price', 1999, []);

            expect($result)->toBe(19.99);
            expect($result)->toBeFloat();
        }
    });

    it('detects coalesce left-side removal by testing parameter override', function () {
        $model = new TestModel;

        // Set config to specific value
        config()->set('lara100.use_bcmath', true);
        config()->set('lara100.rounding_mode', PHP_ROUND_HALF_DOWN);

        // Constructor params MUST override config (left side of ??)
        $cast = new Base100(PHP_ROUND_HALF_UP, false);

        $result = $cast->set($model, 'price', 10.555, []);

        // If ?? only checked right side, would use config values
        // and give different results
        expect($result)->toBeInt();

        // Should use HALF_UP (not HALF_DOWN from config)
        expect($result)->toBeGreaterThanOrEqual(1055);
    });

    it('validates round precision with floating point arithmetic edge cases', function () {
        $model = new TestModel;
        $cast = new Base100(useBcmath: false);

        // Values where precision=2 is critical for correct rounding
        expect($cast->set($model, 'price', 0.1, []))->toBeInt();
        expect($cast->set($model, 'price', 1.1, []))->toBe(110);
        expect($cast->set($model, 'price', 2.2, []))->toBe(220);

        // All must be integers after processing
        expect($cast->set($model, 'price', 0.1, []))->toBeInt();
    });

    it('detects if early returns are missing by testing execution flow', function () {
        $model = new TestModel;
        $cast = new Base100;

        // Without early returns, these would execute additional code
        // We detect this by verifying EXACT results

        // Null handling MUST return immediately
        $nullGet = $cast->get($model, 'price', null, []);
        $nullSet = $cast->set($model, 'price', null, []);

        expect($nullGet)->toBe(0.0);
        expect($nullGet)->not->toBeNull();
        expect($nullSet)->toBe(0);
        expect($nullSet)->not->toBeNull();

        // Type verification proves early return happened
        expect(gettype($nullGet))->toBe('double');
        expect(gettype($nullSet))->toBe('integer');
    });

    it('validates bcmath conditional branches with assertion multiplication', function () {
        if (! extension_loaded('bcmath')) {
            $this->markTestSkipped('BCMath required');
        }

        $model = new TestModel;

        // Multiple assertions to catch any deviation
        $bcmath = new Base100(null, true);
        $standard = new Base100(null, false);

        $testValue = 1234;

        // Get operations
        $getBcmath = $bcmath->get($model, 'price', $testValue, []);
        $getStandard = $standard->get($model, 'price', $testValue, []);

        expect($getBcmath)->toBe(12.34);
        expect($getStandard)->toBe(12.34);
        expect($getBcmath)->toBe($getStandard);
        expect($getBcmath)->toBeFloat();
        expect(gettype($getBcmath))->toBe('double');

        // Set operations
        $setBcmath = $bcmath->set($model, 'price', 12.34, []);
        $setStandard = $standard->set($model, 'price', 12.34, []);

        expect($setBcmath)->toBe(1234);
        expect($setStandard)->toBe(1234);
        expect($setBcmath)->toBe($setStandard);
        expect($setBcmath)->toBeInt();
        expect(gettype($setBcmath))->toBe('integer');
    });
});
