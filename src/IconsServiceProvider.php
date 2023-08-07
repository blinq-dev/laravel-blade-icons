<?php

namespace Blinq\Icons;

use Blinq\Icons\Packs\Fallback;
use Blinq\Icons\Packs\FontAwesome;
use Blinq\Icons\Packs\Heroicons;
use Blinq\Icons\Packs\Material;
use Blinq\Icons\PackageServiceProvider;
use Blinq\Icons\Components\Icon;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Compilers\BladeCompiler;

class IconsServiceProvider extends PackageServiceProvider
{
    public function register(): void
    {
        $this->registerIconPacks();
        $this->registerConfig();
        $this->registerViews();
        $this->registerRoutes();
        $this->registerCommands();
        $this->registerBladeComponents();
        // $this->registerViewComponentDirectory("../resources/views/components", config('blinq-icons.prefix', null), "blinq");
    }

    public function boot()
    {
        $this->bootAutoInjection();
    }

    public function registerIconPacks()
    {
        IconPack::register(new Fallback());
        IconPack::register(new Heroicons());
        IconPack::register(new FontAwesome());
        IconPack::register(new Material());
    }

    public function registerBladeComponents()
    {
        $this->callAfterResolving(BladeCompiler::class, function () {
            Blade::component(Icon::class, "icon", config('blinq-icons.prefix', null));
        });
    }

    public function registerCommands()
    {
        $this->commands([
            Commands\DownloadCommand::class,
        ]);
    }

    public function registerRoutes()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }

    protected function registerConfig()
    {
        $config = __DIR__.'/../config/blinq-icons.php';

        $this->mergeConfigFrom($config, 'blinq-icons');
        $this->publishes([$config => config_path('blinq-icons.php')], ['blinq-icons', 'blinq-icons:config']);
    }

    protected function registerViews() {
        $views = __DIR__.'/../resources/views';

        $this->publishes([ $views => base_path("resources/views/vendor/blinq-icons")], ['blinq-icons', 'blinq-icons:views']);

        $this->loadViewsFrom($views, 'blinq-icons');
    }

    protected function bootAutoInjection()
    {
        app('events')->listen(RequestHandled::class, function ($handled) {
            if (! str($handled->response->headers->get('content-type'))->contains('text/html')) return;
            if (! method_exists($handled->response, 'status') || $handled->response->status() !== 200) return;
            // if ((! static::$hasRenderedAComponentThisRequest) && (! static::$forceAssetInjection)) return;
            // if (app(FrontendAssets::class)->hasRenderedScripts) return;

            $html = $handled->response->getContent();

            if (str($html)->contains('</html>')) {
                $handled->response->setContent($this->injectAssets($html));
            }
        });
    }

    protected function injectAssets($html) {
        $html = str($html);

        $scripts = $this->scripts();
        $styles = $this->styles();

        
        if ($html->test('/<\s*head(?:\s|\s[^>])*>/i') && $html->test('/<\s*\/\s*body\s*>/i')) {
            return $html
            ->replaceMatches('/(<\s*head(?:\s|\s[^>])*>)/i', '$1'.$styles)
            ->replaceMatches('/(<\s*\/\s*body\s*>)/i', $scripts.'$1')
            ->toString();
        }

        return $html
            ->replaceMatches('/(<\s*html(?:\s[^>])*>)/i', '$1'.$styles)
            ->replaceMatches('/(<\s*\/\s*html\s*>)/i', $scripts.'$1')
            ->toString();
    }

    protected function scripts() {
        $token = app()->has('session.store') ? csrf_token() : '';

        $lastModified = filemtime(__DIR__ . "/../resources/js/blinq-icons.js");
        $version = substr(md5($lastModified), 0, 8);

        return <<<HTML
            <script src="/blinq-icons/js/blinq-icons.js?v={$version}" data-csrf="{$token}"></script>
        HTML;
    }

    protected function styles() {
        return <<<HTML
            <!-- <link rel="stylesheet" href="/css/blinq-icons.css"> -->
        HTML;
    }

}
