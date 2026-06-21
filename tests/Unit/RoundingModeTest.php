<?php

declare(strict_types=1);

use AichaDigital\Lara100\RoundingMode;

it('exposes the supported rounding cases', function () {
    $names = array_map(fn (RoundingMode $m) => $m->name, RoundingMode::cases());

    expect($names)->toBe(['Up', 'Down', 'Ceiling', 'Floor', 'HalfUp', 'HalfDown', 'HalfEven', 'HalfOdd']);
});
