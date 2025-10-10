<?php

declare(strict_types=1);

use AichaDigital\Lara100\Tests\Models\TestModelWithTrait;

describe('HasBase100 Trait', function () {
    it('applies base100 cast to specified attributes', function () {
        $model = new TestModelWithTrait;

        $model->price = 1999;
        $model->cost  = 1500;
        $model->tax   = 250;
        $model->save();

        $model->refresh();

        expect($model->price)->toBe(1999)
            ->and($model->cost)->toBe(1500)
            ->and($model->tax)->toBe(250);
    });

    it('stores values correctly in database', function () {
        $model = new TestModelWithTrait;

        $model->price = 5999;  // Should store as 59.99
        $model->cost  = 3500;  // Should store as 35.00
        $model->tax   = 1099;  // Should store as 10.99
        $model->save();

        // Check raw database values
        $raw = TestModelWithTrait::query()
            ->selectRaw('price, cost, tax')
            ->find($model->id);

        expect((float) $raw->getAttributes()['price'])->toBe(59.99)
            ->and((float) $raw->getAttributes()['cost'])->toBe(35.00)
            ->and((float) $raw->getAttributes()['tax'])->toBe(10.99);
    });

    it('retrieves values correctly from database', function () {
        $model = new TestModelWithTrait;

        $model->price = 12345;
        $model->cost  = 9999;
        $model->tax   = 1850;
        $model->save();

        $retrieved = TestModelWithTrait::find($model->id);

        expect($retrieved->price)->toBe(12345)
            ->and($retrieved->cost)->toBe(9999)
            ->and($retrieved->tax)->toBe(1850);
    });

    it('works with mass assignment', function () {
        $model = TestModelWithTrait::create([
            'price' => 2999,
            'cost'  => 1800,
            'tax'   => 540,
        ]);

        expect($model->price)->toBe(2999)
            ->and($model->cost)->toBe(1800)
            ->and($model->tax)->toBe(540);
    });

    it('handles zero values with trait', function () {
        $model = new TestModelWithTrait;

        $model->price = 0;
        $model->cost  = 0;
        $model->tax   = 0;
        $model->save();

        $model->refresh();

        expect($model->price)->toBe(0)
            ->and($model->cost)->toBe(0)
            ->and($model->tax)->toBe(0);
    });

    it('handles updates correctly', function () {
        $model = TestModelWithTrait::create([
            'price' => 1000,
            'cost'  => 500,
            'tax'   => 100,
        ]);

        $model->update([
            'price' => 2000,
            'cost'  => 1000,
            'tax'   => 200,
        ]);

        expect($model->fresh()->price)->toBe(2000)
            ->and($model->fresh()->cost)->toBe(1000)
            ->and($model->fresh()->tax)->toBe(200);
    });

    it('can perform arithmetic operations on cast values', function () {
        $model = TestModelWithTrait::create([
            'price' => 1000,  // 10.00
            'cost'  => 600,   // 6.00
            'tax'   => 150,   // 1.50
        ]);

        $total = $model->price + $model->cost + $model->tax;

        expect($total)->toBe(1750); // 17.50 in base-100
    });
});
