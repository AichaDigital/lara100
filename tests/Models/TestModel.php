<?php

declare(strict_types=1);

namespace AichaDigital\Lara100\Tests\Models;

use AichaDigital\Lara100\Casts\Base100;
use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
    protected $fillable = ['price', 'cost', 'tax'];

    protected function casts(): array
    {
        return [
            'price' => Base100::class,
            'cost'  => Base100::class,
            'tax'   => Base100::class,
        ];
    }
}
