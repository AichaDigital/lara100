<?php

declare(strict_types=1);

use AichaDigital\Lara100\Exceptions\InvalidFixedDecimal;
use AichaDigital\Lara100\Exceptions\Lara100Exception;

it('is catchable as the lara100 marker and as InvalidArgumentException', function () {
    $e = InvalidFixedDecimal::negativeScale(-1);

    expect($e)->toBeInstanceOf(Lara100Exception::class)
        ->and($e)->toBeInstanceOf(InvalidArgumentException::class)
        ->and($e->getMessage())->toContain('-1');
});

it('describes a rejected scalar assignment with the offending type', function () {
    $e = InvalidFixedDecimal::nonFixedDecimalAssignment(19);

    expect($e->getMessage())->toContain('int')
        ->and($e->getMessage())->toContain('FixedDecimal');
});

it('describes a non-numeric storage value with the column key and the offending type', function () {
    $e = InvalidFixedDecimal::nonNumericStorage('fd_amount', 'corrupt');

    expect($e)->toBeInstanceOf(InvalidFixedDecimal::class)
        ->and($e->getMessage())->toContain('fd_amount')
        ->and($e->getMessage())->toContain('string');
});
