<?php

namespace Blinq\Icons\Traits;

/**
 * @property IconPackConfig $config
 */
trait WithDownloads
{
    public function getOrDownload($localFile, $remoteFile = null)
    {
        $namespace = $this->config->namespace;
        $path = $this->config->path;

        $local = config('blinq.icons.download_path', base_path('resources/svg')) . "/$namespace/$localFile";

        
        if (!$remoteFile) {
            $remoteFile = $localFile;
        }
        
        if (!file_exists($local)) {
            // $remoteVariant = $this->variants[$variant];
            $url = "$path/$remoteFile";

            $svg = file_get_contents($url);

            $this->createSvgFile($local, $svg);
        }

        return $local;
    }

    public function createSvgFile($localFile, $contents)
    {
        $contents = $this->beforeSvgFileCreated($localFile, $contents);

        $dir = dirname($localFile);
        if (!file_exists($dir)) {
            mkdir($dir, recursive: true);
        }
        file_put_contents($localFile, $contents);

        $this->afterSvgFileCreated($localFile, $contents);
    }

    public function beforeSvgFileCreated(&$localFile, $contents)
    {
        return $contents;
    }

    public function afterSvgFileCreated($localFile, $contents)
    {
        
    }
}
