<?php

namespace Blinq\Icons;

use Blinq\Icons\Components\Icon;
use Blinq\Icons\Traits\WithDiscovery;
use Blinq\Icons\Traits\WithDownloads;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\ComponentAttributeBag;

abstract class IconPack
{
    use WithDiscovery;
    use WithDownloads;

    protected static $packs =[];
    protected static $cache = [];

    public IconPackConfig $config;

    // register
    public static function register(IconPack $pack)
    {
        static::$packs[$pack->config->namespace] = $pack;
    }

    public static function get(string $namespace) : null | IconPack
    {
        $namespace = (string) str($namespace)->before('/');

        return static::$packs[$namespace] ?? null;
    }

    public static function all() : array
    {
        return static::$packs;
    }

    public function __construct()
    {
        $this->config = $this->configure(new IconPackConfig());
        $this->config->validate();
    }

    public function getName()
    {
        return $this->config->name;
    }

    abstract public function configure(IconPackConfig $config);

    public function getIconPath(string $name, string $variant = null) { 
        $variant = $variant ?? $this->config->defaultVariant;
        $url = $this->discover($name, $variant);

        if (!$url) return null;

        return $this->getOrDownload("$variant/$name.svg", $url);
    }

    public function getIcon(string $name, string $variant = null, ComponentAttributeBag|null $attributes = null)
    {
        $iconPath = $this->getIconPath($name, $variant);

        if (!$iconPath) {
            return null;
        }

        if (isset(static::$cache[$iconPath])) {
            return static::$cache[$iconPath];
        }

        if (!file_exists($iconPath)) {
            return null;
        }

        $svg = file_get_contents($iconPath);

        if ($attributes) {
            $firstLine = explode("\n", $svg)[0];
            if ($attributes->has("stroke-width")) {
                // Remove all stroke-width attributes
                $firstLine = preg_replace('/ stroke-width="[^"]*"/', '', $firstLine);
            }
            if ($attributes->has("stroke")) {
                // Remove all stroke attributes
                $firstLine = preg_replace('/ stroke="[^"]*"/', '', $firstLine);
            }
            if ($attributes->has("fill")) {
                // Remove all fill attributes
                $firstLine = preg_replace('/ fill="[^"]*"/', '', $firstLine);
            }
            // concat first line with the rest of the svg
            $svg = $firstLine . "\n" . implode("\n", array_slice(explode("\n", $svg), 1));

            $svg = str_replace("<svg", "<svg " . $attributes->toHtml(), $svg);
        }

        return $svg;
    }

    public static function svg($pack, $name, $atts = [])
    {
        $rendered = Blade::renderComponent(new Icon($pack, $name, lazy: false));
        
        // Remove all comments
        $rendered = preg_replace('/<!--(.|\s)*?-->/', '', $rendered);
    
        // Remove all whitespace
        $rendered = preg_replace('/\s+/', ' ', $rendered);

        // Add attributes to svg
        $rendered = str_replace("<svg", "<svg " . collect($atts)->map(fn($v, $k) => "$k=\"$v\"")->join(' '), $rendered);

        return $rendered;
    }
}
