<?php

namespace Blinq\Icons\Packs;

use Blinq\Icons\IconPack;
use Blinq\Icons\IconPackConfig;

class Material extends IconPack
{
    public function configure(IconPackConfig $config)
    {
        return $config
            ->setName("Material Icons")
            ->setNamespace("material")
            ->setPath("https://raw.githubusercontent.com/google/material-design-icons/master/src")
            // src subdir
            ->setDiscovery("https://api.github.com/repos/google/material-design-icons/git/trees/7162535d3a7d67d25490e5e19a52c1e402ec9d2b?recursive=true")
            ->setDefaultVariant("default");
    }

    public $variantMapping = [
        'materialicons' => 'default',
        'materialiconsoutlined' => 'outlined',
        'materialiconsround' => 'round',
        'materialiconssharp' => 'sharp',
        'materialiconstwotone' => 'twotone',
    ];

    public function beforeDiscoveryFileCreated($contents)
    {
        $result = json_decode($contents, true);
        $tree = $result['tree'] ?? null;

        // Only keep the list of files
        $icons = collect($tree)
            ->filter(fn($x) => str($x['path'])->endsWith('.svg'))
            ->map(fn($x) => [ "url" => str($x['path'])])
            ->keyBy(fn($x) => 
                ($this->variantMapping[(string) str($x['url'])->beforeLast('/')->afterLast("/")] ?? 'other') . "/" . str($x['url'])->after('/')->before('/') . ".svg"
            );

        $variants = $icons->map(fn($x, $key) => str($key)->before('/'))->unique()->values();

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
