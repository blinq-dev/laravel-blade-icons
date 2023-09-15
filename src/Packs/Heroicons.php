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
class Heroicons extends IconPack
{
    public function configure(IconPackConfig $config)
    {
        return $config
            /**
             * Some info the be showed at https://icons.blinq.dev
             */
            ->setName("Heroicons")
            ->setLicense("MIT")
            ->setDescription("Beautiful hand-crafted SVG icons, by the makers of Tailwind CSS.")
            ->setCopyright("By the makers of tailwindcss.com")
            ->setStrokeBased(true, "outline")
            ->setSite("https://heroicons.com")
            /**
             * The namespace is used to select this icon pack
             */
            ->setNamespace("hero2")
            ->setPath("https://raw.githubusercontent.com/tailwindlabs/heroicons/master/src")
            ->setDiscovery("https://api.github.com/repos/tailwindlabs/heroicons/git/trees/master?recursive=1")
            /**
             * The default variant is used when no variant is specified
             */
            ->setDefaultVariant("solid");
    }

    public $variantMapping = [
        '24/solid' => "solid",
        '24/outline' => "outline",
        '20/solid' => "mini",
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
            ->filter(fn($x) => str($x['path'])->startsWith('src') && str($x['path'])->endsWith('.svg'))
            // the url part of the discovery file. E.g: 20/solid/academic-cap.svg
            ->map(fn($x) => [ "url" => str($x['path'])->replaceFirst("src/", "")])
            // key by the variant and the icon.png. E.g: mini/academic-cap.svg
            ->keyBy(fn($x) => 
                ($this->variantMapping[(string) str($x['url'])->beforeLast('/')] ?? 'other') . "/" . str($x['url'])->afterLast('/')
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
        $isSolid = strpos($localFile, 'solid') !== false || strpos($localFile, 'mini') !== false;

        // Replace ALL occurences of width="??" with nothing
        $contents = preg_replace('/ width="[^"]*"/', '', $contents);
        // Replace ALL occurences of height="??" with nothing
        $contents = preg_replace('/ height="[^"]*"/', '', $contents);

        if ($isSolid) {
            // Replace all occurences of fill="??" with nothing
            $contents = preg_replace('/ fill="[^"]*"/', '', $contents);
            
            // Add fill="currentColor" after <svg
            $contents = preg_replace('/<svg/', '<svg fill="currentColor"', $contents);
        } else {
            // Replace stroke="#0F172A" stroke-width="1.5" with nothing
            $contents = preg_replace('/ stroke="[^"]*" stroke-width="[^"]*"/', '', $contents);
            // Replace fill="none" with stroke="currentColor" stroke-width="1.5"
            $contents = preg_replace('/<svg/', '<svg stroke="currentColor" stroke-width="1.5"', $contents);
        }

        // Write
        return $contents;
    }
}
