<?php

namespace Blinq\Icons;

use Illuminate\Support\Facades\Blade;
use Spatie\LaravelPackageTools\PackageServiceProvider as LaravelPackageToolsPackageServiceProvider;
use Spatie\LaravelPackageTools\Package;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Spatie\LaravelPackageTools\Exceptions\InvalidPackage;
use Illuminate\Support\Str;
use Illuminate\View\Compilers\BladeCompiler;

abstract class PackageServiceProvider extends ServiceProvider
{
    public function getBasePath($subdir = null)
    {
        return __DIR__ . "/" . ($subdir ? $subdir . "/" : "");
    }

    public function registerHelperDirectory(string $directory = null, bool $inGlobalScope = true)
    {
        $directory = $this->getBasePath($directory ?? 'Helpers');

        if (!File::exists($directory)) throw (new InvalidPackage("The helper directory `{$directory}` does not exist"));

        foreach (scandir($directory) as $helperFile) {
            $path = $directory . "/" . $helperFile;

            if (! is_file($path)) {
                continue;
            }

            $function = Str::before($helperFile, '.php');

            if ($inGlobalScope && function_exists($function)) {
                continue;
            }

            require_once $path;
        }
    }

    public function registerMacroDirectory(string $directory)
    {
        $directory = $this->getBasePath($directory ?? 'Helpers');

        if (!File::exists($directory)) throw (new InvalidPackage("The macro directory `{$directory}` does not exist"));
        
        foreach (scandir($directory) as $extensionFile) {
            $path = $directory . "/" . $extensionFile;

            if (substr($extensionFile, 0, 1) === ".") continue;

            if (! is_file($path)) {
                continue;
            }

            require_once $path;
        }
    }

    public function registerViewComponentDirectory(string $directory, string|null $alias = null, string $prefix = null)
    {
        $directory = $this->getBasePath($directory);
        
        $this->callAfterResolving(BladeCompiler::class, function () use($directory, $alias, $prefix) {
            foreach (File::allFiles($directory) as $file) {
                $component = $file->getRelativePathname();
                
                
                $this->registerViewComponent($component, $directory, $alias, $prefix);
            }
        });
    }

        /**
     * Register the given component.
     *
     * @param  string  $component
     * @return void
     */
    protected function registerViewComponent(string $component, string $directory, string|null $alias = null, string $prefix = null)
    {
        $fullName = "blinq.icons";
        $view = str_replace(".blade.php", "", $component);
        $view = str_replace("/", ".", $view);

        $parentDirectory = str($directory)->afterLast('/');

        Blade::component($fullName . '::' . $parentDirectory . '.' . $view, ($alias ? "$alias." : "") . $view);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                $directory . "/" . $component => resource_path('views/vendor/' . $fullName . '/' . $parentDirectory . '/' . $component),
            ], $parentDirectory . '.' . $view);
        }
    }
}
