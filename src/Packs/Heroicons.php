<?php

namespace Blinq\Icons\Packs;

use Blinq\Icons\IconPack;
use Blinq\Icons\IconPackConfig;

class Heroicons extends IconPack
{
    public function configure(IconPackConfig $config)
    {
        return $config
            ->setNamespace("hero2")
            ->setPath("https://raw.githubusercontent.com/tailwindlabs/heroicons/master/src")
            ->setDiscovery("https://api.github.com/repos/tailwindlabs/heroicons/git/trees/master?recursive=1")
            ->setDefaultVariant("solid");
    }

    public $variantMapping = [
        '24/solid' => "solid",
        '24/outline' => "outline",
        '20/solid' => "mini",
    ];

    public function beforeDiscoveryFileCreated($contents)
    {
        $result = json_decode($contents, true);
        $tree = $result['tree'] ?? null;

        // Only keep the list of files
        $icons = collect($tree)
            ->filter(fn($x) => str($x['path'])->startsWith('src') && str($x['path'])->endsWith('.svg'))
            ->map(fn($x) => [ "url" => str($x['path'])->replaceFirst("src/", "")])
            ->keyBy(fn($x) => 
                ($this->variantMapping[(string) str($x['url'])->beforeLast('/')] ?? 'other') . "/" . str($x['url'])->afterLast('/')
            );

        $variants = $icons->map(fn($x) => str($x['url'])->before('/'))->unique()->values();

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
