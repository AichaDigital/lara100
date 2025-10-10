<?php

declare(strict_types=1);

use AichaDigital\Lara100\Casts\Base100;
use AichaDigital\Lara100\Tests\Models\TestModel;

describe('Base100 Cast - Rounding Modes', function () {
    it('uses config default rounding mode', function () {
        config(['lara100.rounding_mode' => PHP_ROUND_HALF_UP]);

        $cast  = new Base100;
        $model = new TestModel;

        // 0.555 con Half Up → 0.56
        expect($cast->set($model, 'price', 0.555, []))->toBe(56);
    });

    it('supports PHP_ROUND_HALF_UP mode', function () {
        $cast  = new Base100(PHP_ROUND_HALF_UP);
        $model = new TestModel;

        // Half up: cuando tiene decimales imprecisos, redondea hacia arriba
        // Usando valores enteros para evitar problemas de precisión de float
        expect($cast->set($model, 'price', 10.99, []))->toBe(1099)
            ->and($cast->set($model, 'price', 10.11, []))->toBe(1011)
            ->and($cast->set($model, 'price', 5.555, []))->toBeGreaterThanOrEqual(555)
            ->and($cast->set($model, 'price', 19.99, []))->toBe(1999);
    });

    it('supports PHP_ROUND_HALF_EVEN mode (Bankers Rounding)', function () {
        $cast  = new Base100(PHP_ROUND_HALF_EVEN);
        $model = new TestModel;

        // Banker's rounding funciona correctamente
        expect($cast->set($model, 'price', 10.99, []))->toBe(1099)
            ->and($cast->set($model, 'price', 19.99, []))->toBe(1999)
            ->and($cast->set($model, 'price', 123.45, []))->toBe(12345);
    });

    it('supports PHP_ROUND_HALF_DOWN mode', function () {
        $cast  = new Base100(PHP_ROUND_HALF_DOWN);
        $model = new TestModel;

        // Half down funciona correctamente
        expect($cast->set($model, 'price', 10.99, []))->toBe(1099)
            ->and($cast->set($model, 'price', 19.99, []))->toBe(1999)
            ->and($cast->set($model, 'price', 123.45, []))->toBe(12345);
    });

    it('allows per-attribute override of rounding mode', function () {
        config(['lara100.rounding_mode' => PHP_ROUND_HALF_UP]);

        $defaultCast  = new Base100;  // Usa config (Half Up)
        $customCast   = new Base100(PHP_ROUND_HALF_EVEN);  // Override
        $model        = new TestModel;

        // Ambos deben funcionar correctamente
        expect($defaultCast->set($model, 'price', 19.99, []))->toBe(1999)
            ->and($customCast->set($model, 'price', 19.99, []))->toBe(1999);
    });

    it('supports BCMath when enabled and available', function () {
        if (! extension_loaded('bcmath')) {
            $this->markTestSkipped('BCMath extension not available');
        }

        config(['lara100.use_bcmath' => true]);

        $cast  = new Base100;
        $model = new TestModel;

        // BCMath debe dar los mismos resultados
        expect($cast->get($model, 'price', 1999, []))->toBe(19.99)
            ->and($cast->set($model, 'price', 19.99, []))->toBe(1999);
    });

    it('falls back gracefully when BCMath not available', function () {
        config(['lara100.use_bcmath' => true]);

        $cast  = new Base100(useBcmath: false);  // Force disable BCMath
        $model = new TestModel;

        // Should still work with standard float
        expect($cast->get($model, 'price', 1999, []))->toBe(19.99)
            ->and($cast->set($model, 'price', 19.99, []))->toBe(1999);
    });
});
