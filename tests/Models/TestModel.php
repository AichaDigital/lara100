<?php

declare(strict_types=1);

namespace AichaDigital\Lara100\Tests\Models;

use Illuminate\Database\Eloquent\Model;

final class TestModel extends Model
{
    protected $fillable = ['price', 'cost', 'tax'];
}
