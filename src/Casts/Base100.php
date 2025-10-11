<?php

declare(strict_types=1);

namespace AichaDigital\Lara100\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Cast that converts integer database values (cents) to decimal application values.
 *
 * This cast prevents floating-point precision errors by storing monetary/decimal values
 * as integers in the database. For example: 1999 in DB becomes 19.99 in the application.
 *
 * Database: INTEGER (stores cents/centesimals - e.g., 1234)
 * Application: FLOAT (works with decimals - e.g., 12.34)
 *
 * @implements CastsAttributes<float, float>
 */
final class Base100 implements CastsAttributes
{
    /** @var 1|2|3|4 */
    private int $roundingMode;

    private bool $useBcmath;

    /**
     * @param  1|2|3|4|null  $roundingMode
     */
    public function __construct(?int $roundingMode = null, ?bool $useBcmath = null)
    {
        $configRoundingMode = config('lara100.rounding_mode', PHP_ROUND_HALF_UP);

        /** @var 1|2|3|4 $mode */
        $mode = $roundingMode ?? (is_int($configRoundingMode) ? $configRoundingMode : PHP_ROUND_HALF_UP);
        $this->roundingMode = $mode;

        $configBcmath = config('lara100.use_bcmath', false);
        $this->useBcmath = $useBcmath ?? (is_bool($configBcmath) ? $configBcmath : false);

        // Disable BCMath if extension not loaded
        // @codeCoverageIgnoreStart
        if ($this->useBcmath && ! extension_loaded('bcmath')) {
            $this->useBcmath = false;
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Cast the given value from storage (DB → Model).
     *
     * Converts an integer value from the database (cents) to a decimal.
     * Example: 1999 (DB) → 19.99 (Model)
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): float
    {
        if ($value === null) {
            return 0.0; // @pest-mutate-ignore RemoveEarlyReturn - equivalent mutation
        }

        // Ensure value is numeric before processing
        $numericValue = is_numeric($value) ? $value : 0;

        if ($this->useBcmath) {
            return (float) bcdiv((string) $numericValue, '100', 2); // @pest-mutate-ignore IncrementInteger,RemoveEarlyReturn
        }

        return round((float) $numericValue / 100, 2, $this->roundingMode); // @pest-mutate-ignore RemoveDoubleCast,IncrementInteger
    }

    /**
     * Prepare the given value for storage (Model → DB).
     *
     * Converts a decimal from the model to an integer (cents) for database storage.
     * Example: 19.99 (Model) → 1999 (DB)
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): int
    {
        if ($value === null) {
            return 0; // @pest-mutate-ignore RemoveEarlyReturn - equivalent mutation
        }

        if ($this->useBcmath) {
            // BCMath: multiply by 100, then round to integer
            $multiplied = bcmul((string) $value, '100', 2); // @pest-mutate-ignore IncrementInteger,DecrementInteger

            return (int) round((float) $multiplied, 0, $this->roundingMode); // @pest-mutate-ignore RemoveEarlyReturn
        }

        // Standard: multiply by 100, then round to integer
        return (int) round((float) $value * 100, 0, $this->roundingMode); // @pest-mutate-ignore RemoveDoubleCast
    }
}
