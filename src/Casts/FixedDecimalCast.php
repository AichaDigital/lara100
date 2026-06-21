<?php

declare(strict_types=1);

namespace AichaDigital\Lara100\Casts;

use AichaDigital\Lara100\Exceptions\InvalidFixedDecimal;
use AichaDigital\Lara100\ValueObjects\FixedDecimal;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Scale-aware Eloquent cast for FixedDecimal over an integer (unscaled) column.
 *
 * Declared as `FixedDecimalCast::class.':<scale>'` in a model's casts(). Storage
 * plumbing only: assignment accepts FixedDecimal|null. Scalars are rejected so
 * callers choose the conversion (ofUnscaled/ofDecimalString/ofFloat) explicitly.
 *
 * The scale is REQUIRED and has no default: it dictates how the stored integer
 * is interpreted (1999 at scale 2 is 19.99, but at scale 4 it is 0.1999). A
 * silent default would risk misreading a column, so an omitted scale must fail.
 *
 * @implements CastsAttributes<FixedDecimal|null, FixedDecimal|null>
 */
final class FixedDecimalCast implements CastsAttributes
{
    private int $scale;

    public function __construct(int|string $scale)
    {
        $this->scale = (int) $scale;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?FixedDecimal
    {
        if ($value === null) {
            return null;
        }

        if (! is_numeric($value)) {
            throw InvalidFixedDecimal::nonNumericStorage($key, $value);
        }

        return FixedDecimal::ofUnscaled((int) $value, $this->scale);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        if ($value === null) {
            return null;
        }

        if (! $value instanceof FixedDecimal) {
            throw InvalidFixedDecimal::nonFixedDecimalAssignment($value);
        }

        return $value->toScale($this->scale)->unscaledValue();
    }
}
