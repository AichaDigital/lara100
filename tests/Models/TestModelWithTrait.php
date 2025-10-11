<?php

declare(strict_types=1);

namespace AichaDigital\Lara100\Tests\Models;

use AichaDigital\Lara100\Concerns\HasBase100;
use Illuminate\Database\Eloquent\Model;

/**
 * @property float $price
 * @property float $cost
 * @property float $tax
 */
final class TestModelWithTrait extends Model
{
    use HasBase100;

    protected $table = 'test_models';

    protected $fillable = ['price', 'cost', 'tax'];

    protected function base100Attributes(): array
    {
        return ['price', 'cost', 'tax'];
    }
}
