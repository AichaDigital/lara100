<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Rounding Mode
    |--------------------------------------------------------------------------
    |
    | This option controls the default rounding mode used when converting
    | decimal values to integer cents. Different rounding modes may be
    | required depending on your country's tax/accounting regulations.
    |
    | Supported modes:
    | - PHP_ROUND_HALF_UP (default)    - Round half up (0.555 → 0.56)
    | - PHP_ROUND_HALF_DOWN            - Round half down (0.555 → 0.55)
    | - PHP_ROUND_HALF_EVEN            - Banker's rounding (0.555 → 0.56, 0.545 → 0.54)
    | - PHP_ROUND_HALF_ODD             - Round to nearest odd (0.555 → 0.55)
    |
    | HALF_UP is the standard in Spain, EU, and most countries.
    | HALF_EVEN is used in accounting to prevent cumulative bias.
    |
    | Environment variable: LARA100_ROUNDING_MODE
    |
    */

    'rounding_mode' => env('LARA100_ROUNDING_MODE', PHP_ROUND_HALF_UP),

    /*
    |--------------------------------------------------------------------------
    | Use BCMath Extension
    |--------------------------------------------------------------------------
    |
    | Enable this option to use PHP's BCMath extension for arbitrary precision
    | arithmetic. This is useful when dealing with very large amounts.
    |
    | Note: The BCMath extension must be installed and enabled in PHP.
    | If enabled but not available, the package will fallback to standard float.
    |
    | Environment variable: LARA100_USE_BCMATH
    |
    */

    'use_bcmath' => env('LARA100_USE_BCMATH', false),

];
