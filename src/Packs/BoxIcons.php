<?php

namespace Blinq\Icons\Packs;

use Blinq\Icons\IconPack;
use Blinq\Icons\IconPackConfig;

/**
 * BoxIcons icon pack
 * 
 * <x-icon pack="namespace/variant" name="icon name" />
 * <x-icon pack="hero2/solid" name="banknotes" />
 */
class BoxIcons extends IconPack
{
    public function configure(IconPackConfig $config)
    {
        return $config
            /**
             * Some info the be showed at https://icons.blinq.dev
             */
            ->setName("Boxicons")
            ->setLicense("MIT License")
            ->setDescription("Boxicons is a carefully designed open source iconset with 1500+ icons")
            // ->setCopyright("By the makers of tailwindcss.com")
            ->setSite("https://github.com/atisawd/boxicons")
            /**
             * The namespace is used to select this icon pack
             */
            ->setNamespace("boxicons")
            ->setPath("https://raw.githubusercontent.com/atisawd/boxicons/master/svg")
            ->setDiscovery("https://api.github.com/repos/atisawd/boxicons/git/trees/master?recursive=1")
            /**
             * The default variant is used when no variant is specified
             */
            ->setDefaultVariant("outline");
    }

    public $variantMapping = [
        'regular' => "outline",
        'solid' => "solid",
        'logos' => "logos",
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

        // Filter the tree:
        $icons = collect($tree)
            // should start with src and end with .svg
            ->filter(fn($x) => str($x['path'])->startsWith('svg') && str($x['path'])->endsWith('.svg'))
            // the url part of the discovery file. E.g: 20/solid/academic-cap.svg
            ->map(fn($x) => [ "url" => str($x['path'])->replaceFirst("svg/", "")])
            // key by the variant and the icon.png. E.g: mini/academic-cap.svg
            ->keyBy(fn($x) => 
                ($this->variantMapping[(string) str($x['url'])->beforeLast('/')] ?? 'other') . "/" . str($x['url'])->afterLast('/')->after('-')
            );

        // Create a unique list of variants
        $variants = $icons
            ->map(fn($x) =>  $this->variantMapping[(string) str($x['url'])->beforeLast('/')] ?? 'other')
            ->unique()
            ->values();

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
        
        // Write
        return $contents;
    }
}
