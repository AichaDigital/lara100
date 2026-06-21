<?php

declare(strict_types=1);

namespace AichaDigital\Lara100\ValueObjects;

use AichaDigital\Lara100\Exceptions\InvalidFixedDecimal;
use AichaDigital\Lara100\RoundingMode;
use Brick\Math\BigDecimal;
use Brick\Math\Exception\MathException;
use Brick\Math\RoundingMode as EngineRoundingMode;
use JsonSerializable;

/**
 * Immutable, scale-configurable exact decimal.
 *
 * Wraps one Brick\Math\BigDecimal. The arithmetic engine is fully encapsulated:
 * the public API speaks lara100's own RoundingMode and exceptions; the only
 * place an engine type leaks is the explicit toBigDecimal() escape hatch.
 */
final class FixedDecimal implements JsonSerializable
{
    private function __construct(private readonly BigDecimal $value) {}

    public static function ofUnscaled(int $unscaled, int $scale): self
    {
        if ($scale < 0) {
            throw InvalidFixedDecimal::negativeScale($scale);
        }

        return new self(BigDecimal::ofUnscaledValue($unscaled, $scale));
    }

    public static function ofDecimalString(string $value, ?int $scale = null): self
    {
        if ($scale !== null && $scale < 0) {
            throw InvalidFixedDecimal::negativeScale($scale);
        }

        try {
            $decimal = BigDecimal::of($value);

            if ($scale !== null) {
                $decimal = $decimal->toScale($scale, self::mapRounding(RoundingMode::HalfUp));
            }
        } catch (MathException $e) {
            throw InvalidFixedDecimal::fromEngine($e);
        }

        return new self($decimal);
    }

    public static function ofFloat(float $value, int $scale, RoundingMode $mode = RoundingMode::HalfUp): self
    {
        if (is_nan($value) || is_infinite($value)) {
            throw InvalidFixedDecimal::nonFiniteFloat($value);
        }

        if ($scale < 0) {
            throw InvalidFixedDecimal::negativeScale($scale);
        }

        // brick/math 0.14.x does not have BigDecimal::fromFloatShortest().
        // Casting to string produces the shortest round-trip representation,
        // which is the semantic equivalent and the approach recommended by the
        // library (passing raw floats is deprecated since 0.14 and removed in 0.15).
        return new self(BigDecimal::of((string) $value)->toScale($scale, self::mapRounding($mode)));
    }

    public static function zero(int $scale = 0): self
    {
        return self::ofUnscaled(0, $scale);
    }

    public function plus(self $other): self
    {
        return new self($this->value->plus($other->value));
    }

    public function minus(self $other): self
    {
        return new self($this->value->minus($other->value));
    }

    public function multipliedBy(int|self $factor): self
    {
        $operand = $factor instanceof self ? $factor->value : $factor;

        return new self($this->value->multipliedBy($operand));
    }

    public function dividedBy(int|self $divisor, int $scale, RoundingMode $mode): self
    {
        if ($scale < 0) {
            throw InvalidFixedDecimal::negativeScale($scale);
        }

        $operand = $divisor instanceof self ? $divisor->value : $divisor;

        try {
            return new self($this->value->dividedBy($operand, $scale, self::mapRounding($mode)));
        } catch (MathException $e) {
            throw InvalidFixedDecimal::fromEngine($e);
        }
    }

    public function toScale(int $scale, RoundingMode $mode = RoundingMode::HalfUp): self
    {
        if ($scale < 0) {
            throw InvalidFixedDecimal::negativeScale($scale);
        }

        return new self($this->value->toScale($scale, self::mapRounding($mode)));
    }

    public function negated(): self
    {
        return new self($this->value->negated());
    }

    public function abs(): self
    {
        return new self($this->value->abs());
    }

    public function scale(): int
    {
        return $this->value->getScale();
    }

    public function unscaledValue(): int
    {
        try {
            return $this->value->getUnscaledValue()->toInt();
        } catch (MathException $e) {
            throw InvalidFixedDecimal::unscaledOverflow((string) $this->value->getUnscaledValue());
        }
    }

    public function toDecimalString(): string
    {
        return (string) $this->value;
    }

    /**
     * Boundary: returns a float and reintroduces imprecision. Use only for
     * display/legacy interop; prefer toDecimalString() / unscaledValue().
     */
    public function toFloat(): float
    {
        return $this->value->toFloat();
    }

    /**
     * Escape hatch: the ONLY place the arithmetic engine's type is exposed.
     * Reach for this only when you genuinely need raw brick/math.
     */
    public function toBigDecimal(): BigDecimal
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return (string) $this->value;
    }

    private static function mapRounding(RoundingMode $mode): EngineRoundingMode
    {
        return match ($mode) {
            RoundingMode::Up => EngineRoundingMode::Up,
            RoundingMode::Down => EngineRoundingMode::Down,
            RoundingMode::Ceiling => EngineRoundingMode::Ceiling,
            RoundingMode::Floor => EngineRoundingMode::Floor,
            RoundingMode::HalfUp => EngineRoundingMode::HalfUp,
            RoundingMode::HalfDown => EngineRoundingMode::HalfDown,
            RoundingMode::HalfEven => EngineRoundingMode::HalfEven,
        };
    }
}
