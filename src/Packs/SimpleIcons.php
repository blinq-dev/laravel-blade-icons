<?php

namespace Blinq\Icons\Packs;

use Blinq\Icons\IconPack;
use Blinq\Icons\IconPackConfig;

/**
 * Heroicons icon pack
 * 
 * <x-icon pack="namespace/variant" name="icon name" />
 * <x-icon pack="hero2/solid" name="banknotes" />
 */
class SimpleIcons extends IconPack
{
    public function configure(IconPackConfig $config)
    {
        return $config
            /**
             * Some info the be showed at https://icons.blinq.dev
             */
            ->setName("SimpleIcons")
            ->setLicense("Creative Commons Zero v1.0 Universal")
            ->setDescription("Over 2600 Free SVG icons for popular brands")
            // ->setCopyright("By the makers of tailwindcss.com")
            ->setSite("https://simpleicons.org/")
            /**
             * The namespace is used to select this icon pack
             */
            ->setNamespace("simpleicons")
            ->setPath("https://raw.githubusercontent.com/simple-icons/simple-icons/develop/icons")
            ->setDiscovery("https://api.github.com/repos/simple-icons/simple-icons/git/trees/2c237460f40167173bad269aa3bc0257b81594b7?recursive=1")
            /**
             * The default variant is used when no variant is specified
             */
            ->setDefaultVariant("default");
    }

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

        // Filter the tree:
        $icons = collect($tree)
            // should start with src and end with .svg
            ->filter(fn($x) => str($x['path'])->endsWith('.svg'))
            // the url part of the discovery file. E.g: 20/solid/academic-cap.svg
            ->map(fn($x) => [ "url" => $x['path']])
            // key by the variant and the icon.png. E.g: mini/academic-cap.svg
            ->keyBy(fn($x) => 
                "default/" . $x['url']
            );

        // Create a unique list of variants
        $variants = ['default'];

        // Combine them
        return [
            "variants" => $variants,
            "icons" => $icons,
        ];
    }
    
    public function beforeSvgFileCreated(&$localFile, $contents)
    {
        $contents = preg_replace('/<svg/', '<svg fill="currentColor"', $contents);

        // Write
        return $contents;
    }
}
