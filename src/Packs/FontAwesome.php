<?php

namespace Blinq\Icons\Packs;

use Blinq\Icons\IconPack;
use Blinq\Icons\IconPackConfig;

class FontAwesome extends IconPack
{
    public function configure(IconPackConfig $config)
    {
        return $config
            ->setNamespace("fa6")
            ->setPath("https://raw.githubusercontent.com/FortAwesome/Font-Awesome/6.x/svgs")
            ->setDiscovery("https://api.github.com/repos/FortAwesome/Font-Awesome/git/trees/6.x?recursive=1")
            ->setDefaultVariant("solid");
    }

    public function beforeDiscoveryFileCreated($contents)
    {
        $result = json_decode($contents, true);
        $tree = $result['tree'] ?? null;

        // Only keep the list of files
        $icons = collect($tree)
            ->filter(fn($x) => str($x['path'])->startsWith('svgs') && str($x['path'])->endsWith('.svg'))
            ->map(fn($x) => [ "url" => str($x['path'])->replaceFirst("svgs/", "")])
            ->keyBy('url');

        $variants = $icons->map(fn($x) => str($x['url'])->before('/'))->unique()->values();

        return [
            "variants" => $variants,
            "icons" => $icons,
        ];
    }

    public function beforeSvgFileCreated(&$localFile, $contents)
    {
        $contents = preg_replace('/<svg/', '<svg fill="currentColor"', $contents);

        return $contents;
    }


}
