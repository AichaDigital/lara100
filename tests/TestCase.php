<?php

declare(strict_types=1);

namespace AichaDigital\Lara100\Tests;

use AichaDigital\Lara100\Lara100ServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            Lara100ServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');

        $migration = include __DIR__.'/database/migrations/create_test_models_table.php';

        // @phpstan-ignore-next-line - Migration anonymous class from file
        $migration->up();
    }
}
