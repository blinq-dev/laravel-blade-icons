<?php

namespace Blinq\Icons\Packs;

use Blinq\Icons\IconPack;
use Blinq\Icons\IconPackConfig;

class Fallback extends IconPack
{
    public function configure(IconPackConfig $config)
    {
        return $config
            ->setNamespace("fallback")
            ->setPath(__DIR__ . "/../../resources/svg/fallback")
            ->setDiscovery(__DIR__ . "/../../resources/svg/fallback/discovery.json")
            ->setDefaultVariant("default");
    }
}
