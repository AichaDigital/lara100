<?php

declare(strict_types=1);

use AichaDigital\Lara100\Tests\Models\TestModelWithTrait;

describe('HasBase100 Trait', function () {
    it('applies base100 cast to specified attributes', function () {
        $model = new TestModelWithTrait;

        $model->price = 19.99;  // User sets decimal
        $model->cost  = 15.00;
        $model->tax   = 2.50;
        $model->save();

        $model->refresh();

        expect($model->price)->toBe(19.99)  // User gets decimal
            ->and($model->cost)->toBe(15.00)
            ->and($model->tax)->toBe(2.50);
    });

    it('stores values correctly in database as integers', function () {
        $model = new TestModelWithTrait;

        $model->price = 59.99;  // User sets 59.99
        $model->cost  = 35.00;  // User sets 35.00
        $model->tax   = 10.99;  // User sets 10.99
        $model->save();

        // Check raw database values (should be integers)
        $raw = TestModelWithTrait::query()
            ->selectRaw('price, cost, tax')
            ->find($model->id);

        expect((int) $raw->getAttributes()['price'])->toBe(5999)  // Stored as 5999 cents
            ->and((int) $raw->getAttributes()['cost'])->toBe(3500)  // Stored as 3500 cents
            ->and((int) $raw->getAttributes()['tax'])->toBe(1099);  // Stored as 1099 cents
    });

    it('retrieves values correctly from database', function () {
        $model = new TestModelWithTrait;

        $model->price = 123.45;  // User sets decimals
        $model->cost  = 99.99;
        $model->tax   = 18.50;
        $model->save();

        $retrieved = TestModelWithTrait::find($model->id);

        expect($retrieved->price)->toBe(123.45)  // User gets decimals back
            ->and($retrieved->cost)->toBe(99.99)
            ->and($retrieved->tax)->toBe(18.50);
    });

    it('works with mass assignment', function () {
        $model = TestModelWithTrait::create([
            'price' => 29.99,  // User provides decimals
            'cost'  => 18.00,
            'tax'   => 5.40,
        ]);

        expect($model->price)->toBe(29.99)  // User gets decimals
            ->and($model->cost)->toBe(18.00)
            ->and($model->tax)->toBe(5.40);
    });

    it('handles zero values with trait', function () {
        $model = new TestModelWithTrait;

        $model->price = 0.00;
        $model->cost  = 0.00;
        $model->tax   = 0.00;
        $model->save();

        $model->refresh();

        expect($model->price)->toBe(0.00)
            ->and($model->cost)->toBe(0.00)
            ->and($model->tax)->toBe(0.00);
    });

    it('handles updates correctly', function () {
        $model = TestModelWithTrait::create([
            'price' => 10.00,
            'cost'  => 5.00,
            'tax'   => 1.00,
        ]);

        $model->update([
            'price' => 20.00,
            'cost'  => 10.00,
            'tax'   => 2.00,
        ]);

        expect($model->fresh()->price)->toBe(20.00)
            ->and($model->fresh()->cost)->toBe(10.00)
            ->and($model->fresh()->tax)->toBe(2.00);
    });

    it('can perform arithmetic operations on cast values', function () {
        $model = TestModelWithTrait::create([
            'price' => 10.00,  // $10.00
            'cost'  => 6.00,   // $6.00
            'tax'   => 1.50,   // $1.50
        ]);

        $total = $model->price + $model->cost + $model->tax;

        expect($total)->toBe(17.50);  // $17.50 (works perfectly with floats now!)
    });
});
