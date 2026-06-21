<?php

declare(strict_types=1);

use AichaDigital\Lara100\Casts\Base100;
use AichaDigital\Lara100\Tests\Models\TestModel;
use AichaDigital\Lara100\ValueObjects\FixedDecimal;

it('documents that the deprecated float Base100 cast reintroduces drift', function () {
    $cast = new Base100;
    $model = new TestModel;

    $a = $cast->get($model, 'price', 10, []);  // 0.10 (float)
    $b = $cast->get($model, 'price', 20, []);  // 0.20 (float)

    // The float path is NOT exact: 0.1 + 0.2 !== 0.3
    expect($a + $b === 0.3)->toBeFalse();

    // FixedDecimal is exact for the same values.
    $exact = FixedDecimal::ofDecimalString('0.10')->plus(FixedDecimal::ofDecimalString('0.20'));
    expect($exact->toDecimalString())->toBe('0.30');
});
