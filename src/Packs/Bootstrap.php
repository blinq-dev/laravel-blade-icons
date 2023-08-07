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
class Bootstrap extends IconPack
{
    public function configure(IconPackConfig $config)
    {
        return $config
            /**
             * Some info the be showed at https://icons.blinq.dev
             */
            ->setName("Bootstrap Icons")
            ->setLicense("MIT License")
            ->setDescription("Free, high quality, open source icon library with over 1,800 icons.")
            // ->setCopyright("By the makers of tailwindcss.com")
            ->setSite("https://icons.getbootstrap.com/")
            /**
             * The namespace is used to select this icon pack
             */
            ->setNamespace("bs")
            ->setPath("https://raw.githubusercontent.com/twbs/icons/main/icons")
            ->setDiscovery("https://api.github.com/repos/twbs/icons/git/trees/8d69212c66e5f7b612c0ce9cd6c3a3751046c1d6?recursive=1")
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

        // Replace ALL occurences of width="??" with nothing
        $contents = preg_replace('/ width="[^"]*"/', '', $contents);
        // Replace ALL occurences of height="??" with nothing
        $contents = preg_replace('/ height="[^"]*"/', '', $contents);
        // Remove class
        $contents = preg_replace('/ class="[^"]*"/', '', $contents);

        // Write
        return $contents;
    }
}
