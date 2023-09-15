<?php

namespace Blinq\Icons\Packs;

use Blinq\Icons\IconPack;
use Blinq\Icons\IconPackConfig;

/**
 * Akar icon pack
 * 
 * <x-icon id="icon name@namespace/variant" />
 * <x-icon id="banknotes@hero2/solid" />
 */
class Akar extends IconPack
{
    public function configure(IconPackConfig $config)
    {
        return $config
            /**
             * Some info the be showed at https://icons.blinq.dev
             */
            ->setName("Akar")
            ->setLicense("MIT License")
            ->setDescription("A perfectly rounded icon library made for designers, developers, and pretty much everyone.")
            // ->setCopyright("By the makers of tailwindcss.com")
            ->setSite("https://akaricons.com/")
            /**
             * The namespace is used to select this icon pack
             */
            ->setNamespace("akar")
            ->setPath("https://raw.githubusercontent.com/artcoholic/akar-icons/master/src/svg")
            ->setDiscovery("https://api.github.com/repos/artcoholic/akar-icons/git/trees/dd79c7a3d029d9cb3e49787bdbf806c321145ee9?recursive=1")
            ->setStrokeBased(true)
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
        // Replace ALL occurences of width="??" with nothing
        $contents = preg_replace('/ width="[^"]*"/', '', $contents);
        // Replace ALL occurences of height="??" with nothing
        $contents = preg_replace('/ height="[^"]*"/', '', $contents);
        
        $contents = preg_replace('/ stroke="[^"]*" stroke-width="[^"]*"/', '', $contents);
        $contents = preg_replace('/<svg/', '<svg stroke="currentColor" stroke-width="1.5" ', $contents);

        // Write
        return $contents;
    }
}
