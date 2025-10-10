<?php

declare(strict_types=1);

namespace AichaDigital\Lara100\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Cast that converts decimal database values to base-100 integers (cents/centesimals).
 *
 * This cast prevents floating-point precision errors by storing decimals as integers
 * multiplied by 100. For example: 19.99 in DB becomes 1999 in the application.
 *
 * @implements CastsAttributes<int, float>
 */
class Base100 implements CastsAttributes
{
    /**
     * Cast the given value from storage (DB → Model).
     *
     * Converts a decimal value from the database to an integer multiplied by 100.
     * Example: 19.99 (DB) → 1999 (Model)
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): int
    {
        if ($value === null) {
            return 0;
        }

        return (int) round((float) $value * 100);
    }

    /**
     * Prepare the given value for storage (Model → DB).
     *
     * Converts an integer from the model to a decimal divided by 100 for database storage.
     * Example: 1999 (Model) → 19.99 (DB)
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): float
    {
        if ($value === null) {
            return 0.0;
        }

        return round((float) $value / 100, 2);
    }
}
