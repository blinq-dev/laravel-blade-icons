<?php

namespace Blinq\Icons\Traits;

use Blinq\Icons\IconPackConfig;

/**
 * @property IconPackConfig $config
 */
trait WithDiscovery
{
    static $discoveryCache = [];

    public function getDiscoveryList()
    {
        $cacheKey = $this->config->namespace . ".discovery";
        if (isset(static::$discoveryCache[$cacheKey])) {
            return static::$discoveryCache[$cacheKey];
        }

        $file = $this->getOrDownloadDiscovery();

        $decoded = json_decode($file, true);

        static::$discoveryCache[$cacheKey] = $decoded;

        return $decoded;
    }

    public function discover($name, $variant)
    {
        $list = $this->getDiscoveryList();

        return $list['icons']["$variant/$name.svg"]['url'] ?? null;
    }

    public function list()
    {
        $cacheKey = $this->config->namespace . ".list";

        if (isset(static::$discoveryCache[$cacheKey])) {
            return static::$discoveryCache[$cacheKey];
        }

        $list = $this->getDiscoveryList();

        $items = collect($list['icons'])
            ->map(fn($x, $key) => (object) [
                'name' => (string) str($key)->afterLast('/')->beforeLast('.'),
                'variant' => (string) str($key)->before('/'),
                'url' => $x['url'],
            ]);
        
        static::$discoveryCache[$cacheKey] = $items;

        return $items;
    }

    public function getOrDownloadDiscovery()
    {
        $discoveryPath = $this->config->discovery;
        $namespace = $this->config->namespace;

        if (!isset($discoveryPath)) {
            return null;
        }
        $local = config('blinq.icons.download_path', base_path('resources/svg')) . "/$namespace/discovery.json";

        if (!file_exists($local)) {
            try {
                // Create a stream
                $opts = [
                    "http" => [
                        "method" => "GET",
                        "header" => "User-Agent: Blinq Icons"
                    ]
                ];
                $context = stream_context_create($opts);

                $contents = file_get_contents($discoveryPath, false, $context);
            } catch (\Throwable $th) {
                throw new \Exception("Could not download discovery file from `$discoveryPath` for `$namespace`");
            }
            
            $this->createDiscoveryFile($local, $contents);
        }

        try {
            $contents = file_get_contents($local);
        } catch (\Throwable $th) {
            throw new \Exception("Could read discovery file (`$local`) for `$namespace`");
        }

        return $contents;
    }

    public function createDiscoveryFile($localFile, $contents)
    {
        $contents = $this->beforeDiscoveryFileCreated($contents);

        if (is_array($contents)) {
            $contents = json_encode($contents);
        }

        $dir = dirname($localFile);
        if (!file_exists($dir)) {
            mkdir($dir, recursive: true);
        }
        file_put_contents($localFile, $contents);

        $this->afterDiscoveryFileCreated($contents);
    }

    public function beforeDiscoveryFileCreated($contents)
    {
        return $contents;
    }
    public function afterDiscoveryFileCreated($contents)
    {

    }
}
