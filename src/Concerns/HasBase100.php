<?php

declare(strict_types=1);

namespace AichaDigital\Lara100\Concerns;

use AichaDigital\Lara100\Casts\Base100;

/**
 * Trait to simplify applying Base100 cast to multiple attributes.
 *
 * Usage:
 * ```php
 * class Product extends Model
 * {
 *     use HasBase100;
 *
 *     protected function base100Attributes(): array
 *     {
 *         return ['price', 'cost', 'tax'];
 *     }
 * }
 * ```
 */
trait HasBase100
{
    /**
     * Get the list of attributes that should use the Base100 cast.
     *
     * @return array<int, string>
     */
    abstract protected function base100Attributes(): array;

    /**
     * Initialize the HasBase100 trait for an instance.
     *
     * This method is automatically called by Laravel when the model is instantiated.
     * It applies the Base100 cast to all attributes defined in base100Attributes().
     */
    protected function initializeHasBase100(): void
    {
        foreach ($this->base100Attributes() as $attribute) {
            $this->casts[$attribute] = Base100::class;
        }
    }
}
