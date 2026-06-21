<?php

declare(strict_types=1);

namespace AichaDigital\Lara100;

/**
 * lara100-owned rounding modes.
 *
 * Mirrors the modes lara100 supports. Mapped to the arithmetic engine inside
 * FixedDecimal so the public API never depends on the engine's own enum.
 */
enum RoundingMode
{
    case Up;
    case Down;
    case Ceiling;
    case Floor;
    case HalfUp;
    case HalfDown;
    case HalfEven;
    case HalfOdd;
}
