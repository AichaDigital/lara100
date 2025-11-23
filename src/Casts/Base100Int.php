<?php

declare(strict_types=1);

namespace AichaDigital\Lara100\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Cast for base-100 values that remain as INTEGER in the model.
 *
 * This cast is designed for financial calculations, fiscal compliance, and API responses
 * where floating-point precision errors are unacceptable.
 *
 * Database: INTEGER (stores cents/centesimals - e.g., 1234)
 * Application: INTEGER (remains as cents - e.g., 1234)
 * Display: Use Money::format($value) or similar for formatted output
 *
 * @implements CastsAttributes<int, int>
 */
final class Base100Int implements CastsAttributes
{
    /**
     * Cast the given value from storage (DB → Model).
     *
     * Returns the integer value as-is from the database.
     * Example: 1234 (DB) → 1234 (Model)
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): int
    {
        if ($value === null) {
            return 0;
        }

        // Ensure value is numeric before converting to int
        if (! is_numeric($value)) {
            return 0;
        }

        return (int) $value;
    }

    /**
     * Prepare the given value for storage (Model → DB).
     *
     * Stores the integer value as-is to the database.
     * Example: 1234 (Model) → 1234 (DB)
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): int
    {
        if ($value === null) {
            return 0;
        }

        // Ensure value is numeric before converting to int
        if (! is_numeric($value)) {
            return 0;
        }

        return (int) $value;
    }
}
