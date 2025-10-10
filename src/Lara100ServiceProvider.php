<?php

declare(strict_types=1);

namespace AichaDigital\Lara100;

use Spatie\LaravelPackageTools\{Package, PackageServiceProvider};

class Lara100ServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('lara100');
    }
}
