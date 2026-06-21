<?php

declare(strict_types=1);

namespace AichaDigital\Lara100\Exceptions;

use InvalidArgumentException;
use Throwable;

final class InvalidFixedDecimal extends InvalidArgumentException implements Lara100Exception
{
    public static function negativeScale(int $scale): self
    {
        return new self("Scale must be zero or positive, got {$scale}.");
    }

    public static function nonFiniteFloat(float $value): self
    {
        return new self('Cannot build a FixedDecimal from a non-finite float (NAN/INF).');
    }

    public static function fromEngine(Throwable $previous): self
    {
        return new self('Invalid decimal value: '.$previous->getMessage(), 0, $previous);
    }

    public static function unscaledOverflow(string $value): self
    {
        return new self("Unscaled value {$value} exceeds the PHP integer range.");
    }

    public static function nonFixedDecimalAssignment(mixed $value): self
    {
        $type = get_debug_type($value);

        return new self(
            "FixedDecimalCast accepts only FixedDecimal|null on assignment, got {$type}. "
            .'Build it explicitly via FixedDecimal::ofUnscaled(), ofDecimalString(), or ofFloat().'
        );
    }
}
