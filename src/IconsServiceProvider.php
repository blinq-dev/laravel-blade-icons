<?php

namespace Blinq\Icons;

use Blinq\Icons\Commands\ImportCommand;
use Blinq\Icons\Packs\Fallback;
use Blinq\Icons\Packs\FontAwesome;
use Blinq\Icons\Packs\Heroicons;
use Blinq\Icons\Packs\Material;
use Blinq\Icons\PackageServiceProvider;
use Spatie\LaravelPackageTools\Package;

class IconsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('icons')
            ->setBasePath(__DIR__)
            ->hasViews()
            // ->hasMigration('create_ui_table')
            ->hasCommand(ImportCommand::class);

        IconPack::register(new Fallback());
        IconPack::register(new Heroicons());
        IconPack::register(new FontAwesome());
        IconPack::register(new Material());
    }
    
    public function packageRegistered()
    {
        // add a config file
        $this->mergeConfigFrom(__DIR__ . '/../config/blinq.icons.php', 'blinq.icons');

        // $this->registerHelperDirectory("Helpers", inGlobalScope: true);
        $this->registerViewComponentDirectory("../resources/views/components", "ui", null);
    }

    

}
