<?php

declare(strict_types=1);

use AichaDigital\Lara100\Casts\Base100Int;
use AichaDigital\Lara100\Tests\Models\TestModel;

describe('Base100Int Cast', function () {
    it('returns integer from database as-is', function () {
        $cast = new Base100Int;
        $model = new TestModel;

        expect($cast->get($model, 'amount', 1234, []))->toBe(1234)
            ->and($cast->get($model, 'amount', 1999, []))->toBe(1999)
            ->and($cast->get($model, 'amount', 100, []))->toBe(100)
            ->and($cast->get($model, 'amount', 1, []))->toBe(1)
            ->and($cast->get($model, 'amount', 0, []))->toBe(0);
    });

    it('stores integer to database as-is', function () {
        $cast = new Base100Int;
        $model = new TestModel;

        expect($cast->set($model, 'amount', 1234, []))->toBe(1234)
            ->and($cast->set($model, 'amount', 1999, []))->toBe(1999)
            ->and($cast->set($model, 'amount', 100, []))->toBe(100)
            ->and($cast->set($model, 'amount', 1, []))->toBe(1)
            ->and($cast->set($model, 'amount', 0, []))->toBe(0);
    });

    it('handles null as zero', function () {
        $cast = new Base100Int;
        $model = new TestModel;

        expect($cast->get($model, 'amount', null, []))->toBe(0)
            ->and($cast->set($model, 'amount', null, []))->toBe(0);
    });

    it('handles negative values', function () {
        $cast = new Base100Int;
        $model = new TestModel;

        expect($cast->get($model, 'amount', -5025, []))->toBe(-5025)
            ->and($cast->set($model, 'amount', -5025, []))->toBe(-5025);
    });

    it('returns correct types', function () {
        $cast = new Base100Int;
        $model = new TestModel;

        $getValue = $cast->get($model, 'amount', 10000, []);
        $setValue = $cast->set($model, 'amount', 10000, []);

        expect($getValue)->toBeInt()
            ->and($setValue)->toBeInt();
    });

    it('handles large numbers', function () {
        $cast = new Base100Int;
        $model = new TestModel;

        expect($cast->get($model, 'amount', 99999999, []))->toBe(99999999)
            ->and($cast->set($model, 'amount', 99999999, []))->toBe(99999999);
    });

    it('handles non-numeric values safely', function () {
        $cast = new Base100Int;
        $model = new TestModel;

        expect($cast->get($model, 'amount', 'invalid', []))->toBe(0)
            ->and($cast->set($model, 'amount', 'invalid', []))->toBe(0);
    });

    it('converts float inputs to integers on set', function () {
        $cast = new Base100Int;
        $model = new TestModel;

        // When setting float values, they are truncated to integers
        expect($cast->set($model, 'amount', 123.45, []))->toBe(123)
            ->and($cast->set($model, 'amount', 999.99, []))->toBe(999)
            ->and($cast->set($model, 'amount', 0.99, []))->toBe(0);
    });

    it('converts string numeric values correctly', function () {
        $cast = new Base100Int;
        $model = new TestModel;

        expect($cast->get($model, 'amount', '1234', []))->toBe(1234)
            ->and($cast->set($model, 'amount', '1234', []))->toBe(1234);
    });
});

describe('Base100Int for financial calculations', function () {
    it('maintains precision in calculations without floating point errors', function () {
        $cast = new Base100Int;
        $model = new TestModel;

        // Simulate 100 invoice lines of 1999 cents each
        $total = 0;
        for ($i = 0; $i < 100; $i++) {
            $lineAmount = $cast->get($model, 'amount', 1999, []);
            $total += $lineAmount;
        }

        // Should be exactly 199900 cents (€1999.00)
        expect($total)->toBe(199900)
            ->and($total)->toBeInt();
    });

    it('calculates tax amounts without precision errors', function () {
        $cast = new Base100Int;
        $model = new TestModel;

        // Product price: €100.00 = 10000 cents
        $taxableAmount = $cast->get($model, 'taxable_amount', 10000, []);

        // Tax rate: 21% = 2100 cents (in base 100)
        // Tax amount: 10000 * 21 / 100 = 2100 cents
        $taxAmount = (int) round($taxableAmount * 21 / 100);

        // Total: 10000 + 2100 = 12100 cents
        $totalAmount = $taxableAmount + $taxAmount;

        expect($taxableAmount)->toBe(10000)
            ->and($taxAmount)->toBe(2100)
            ->and($totalAmount)->toBe(12100)
            ->and($totalAmount)->toBeInt();
    });

    it('handles invoice totals for compliance', function () {
        $cast = new Base100Int;
        $model = new TestModel;

        // Simulate invoice with multiple items
        $items = [
            ['unit_price' => 1999, 'quantity' => 2, 'tax_rate' => 21],  // €19.99 x 2
            ['unit_price' => 2500, 'quantity' => 1, 'tax_rate' => 21],  // €25.00 x 1
            ['unit_price' => 999, 'quantity' => 3, 'tax_rate' => 10],   // €9.99 x 3
        ];

        $taxableTotal = 0;
        $taxTotal = 0;

        foreach ($items as $item) {
            $unitPrice = $cast->get($model, 'unit_price', $item['unit_price'], []);
            $lineAmount = $unitPrice * $item['quantity'];
            $lineTax = (int) round($lineAmount * $item['tax_rate'] / 100);

            $taxableTotal += $lineAmount;
            $taxTotal += $lineTax;
        }

        $grandTotal = $taxableTotal + $taxTotal;

        // All values should be integers
        expect($taxableTotal)->toBeInt()
            ->and($taxTotal)->toBeInt()
            ->and($grandTotal)->toBeInt();

        // Verify exact calculations
        // Item 1: 1999 * 2 = 3998, tax = 840
        // Item 2: 2500 * 1 = 2500, tax = 525
        // Item 3: 999 * 3 = 2997, tax = 300
        // Total taxable: 9495, Total tax: 1665
        expect($taxableTotal)->toBe(9495)
            ->and($taxTotal)->toBe(1665)
            ->and($grandTotal)->toBe(11160);
    });
});
