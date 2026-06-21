<?php

declare(strict_types=1);

namespace AichaDigital\Lara100\Tests\Models;

use AichaDigital\Lara100\Casts\FixedDecimalCast;
use Illuminate\Database\Eloquent\Model;

/**
 * @property \AichaDigital\Lara100\ValueObjects\FixedDecimal|null $fd_amount
 * @property \AichaDigital\Lara100\ValueObjects\FixedDecimal|null $fd_rate
 */
class FixedDecimalTestModel extends Model
{
    protected $table = 'test_models';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'fd_amount' => FixedDecimalCast::class.':2',
            'fd_rate' => FixedDecimalCast::class.':4',
        ];
    }
}
