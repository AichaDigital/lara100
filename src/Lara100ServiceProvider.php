<?php

declare(strict_types=1);

namespace AichaDigital\Lara100;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class Lara100ServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('lara100')
            ->hasConfigFile();
    }
}
