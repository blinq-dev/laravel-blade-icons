<?php

namespace Blinq\Icons\Packs;

use Blinq\Icons\IconPack;
use Blinq\Icons\IconPackConfig;

/**
 * Heroicons icon pack
 * 
 * <x-icon id="icon name@namespace/variant" />
 * <x-icon id="banknotes@hero2/solid" />
 */
class FluentUI extends IconPack
{
    public function configure(IconPackConfig $config)
    {
        return $config
            /**
             * Some info the be showed at https://icons.blinq.dev
             */
            ->setName("FluentUI")
            ->setLicense("MIT License")
            ->setDescription("Over 2600 Free SVG icons for popular brands")
            // ->setCopyright("By the makers of tailwindcss.com")
            ->setSite("https://github.com/microsoft/fluentui-system-icons")
            /**
             * The namespace is used to select this icon pack
             */
            ->setNamespace("fu")
            ->setPath("https://raw.githubusercontent.com/microsoft/fluentui-system-icons/main/assets")
            ->setDiscovery("https://api.github.com/repos/microsoft/fluentui-system-icons/git/trees/612178741b6b64dae60ce3583cb54073c03ca395?recursive=1")
            /**
             * The default variant is used when no variant is specified
             */
            ->setDefaultVariant("regular-24");
    }

    public $variantMapping = [
        // '16_filled' => 'filled-16',
        // '16_regular' => 'regular-16',
        '20_filled' => 'filled',
        '20_filled_ltr' => 'filled',
        '20_filled_rtl' => 'filled',
        '20_regular' => 'regular',
        '20_regular_ltr' => 'regular',
        '20_regular_rtl' => 'regular',
    ];


    /**
     * Parses the github trees url and converts it into a discovery
     *
     * @param string $discoveryResponse
     * @return void
     */
    public function beforeDiscoveryFileCreated($discoveryResponse)
    {
        // Turn string into array
        $result = json_decode($discoveryResponse, true);

        // Get the tree
        $tree = $result['tree'] ?? null;

        $variants = [];

        // Filter the tree:
        $icons = collect($tree)
            // should start with src and end with .svg
            ->filter(fn($x) => str($x['path'])->endsWith('.svg') && str($x['path'])->contains('_20_'))
            // the url part of the discovery file. E.g: 20/solid/academic-cap.svg
            ->map(fn($x) => [ "url" => $x['path']])
            // key by the variant and the icon.png. E.g: mini/academic-cap.svg
            ->keyBy(function($x) use(&$variants) {
                $url = $x['url'];
                // ic_fluent_arrow_circle_down_20_regular.svg
                // ic_fluent_arrow_circle_down_16_regular.svg
                // ic_fluent_arrow_circle_down_28_filled.svg

                // use regex for the end
                $regex = '/ic_fluent_(.*)_([\d]{2}_[^\.]*).svg$/';
                preg_match($regex, $url, $matches);

                $name = $matches[1];
                $variant = $matches[2];

                if ($variant && $name) {
                    $variant = $this->variantMapping[$variant] ?? $variant;
                    if (!in_array($variant, $variants)) {
                        $variants[] = $variant;
                    }
                    return $variant . '/' . $name . ".svg";
                }
            });

        // dd($icons->toArray());
        // Combine them
        return [
            "variants" => $variants,
            "icons" => $icons,
        ];
    }
    
    public function beforeSvgFileCreated(&$localFile, $contents)
    {
        $contents = preg_replace('/<svg/', '<svg fill="currentColor"', $contents);

        // Replace ALL occurences of width="??" with nothing
        $contents = preg_replace('/ width="[^"]*"/', '', $contents);
        // Replace ALL occurences of height="??" with nothing
        $contents = preg_replace('/ height="[^"]*"/', '', $contents);

        $contents = preg_replace('/ fill="[^"]*"/', '', $contents);
    
        // Write
        return $contents;
    }
}
