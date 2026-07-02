<?php

declare(strict_types=1);

namespace AichaDigital\Lara100\Tests;

use AichaDigital\Lara100\Lara100ServiceProvider;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    // lara100 is a cast package with no package migrations; the only schema is a
    // test-only `test_models` fixture. It is registered with the migrator and run
    // through RefreshDatabase (up-only), matching the umbrella reference — no
    // manual include/->up(). See the CLAUDE.md lesson (2026-06-27).
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [
            Lara100ServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');

        // Register the test-only fixture directory with the migrator (the same
        // mechanism Laravel's loadMigrationsFrom uses).
        $app->afterResolving('migrator', function (Migrator $migrator): void {
            $migrator->path(__DIR__.'/database/migrations');
        });
    }
}
