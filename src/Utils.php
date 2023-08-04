<?php

namespace Blinq\Icons;

class Utils
{
    static function pretendResponseIsFileFromString($string, $lastModified, $mimeType = 'application/javascript')
    {
        $expires = strtotime('+1 year');
        $cacheControl = 'public, max-age=31536000';

        $headers = [
            'Content-Type' => "$mimeType; charset=utf-8",
            'Expires' => static::httpDate($expires),
            'Cache-Control' => $cacheControl,
            'Last-Modified' => static::httpDate($lastModified),
        ];

        return response($string, 200, $headers);
    }

    static function pretendResponseIsFile($file, $mimeType = 'application/javascript')
    {
        $expires = strtotime('+1 year');
        $lastModified = filemtime($file);
        $cacheControl = 'public, max-age=31536000';

        if (static::matchesCache($lastModified)) {
            return response()->make('', 304, [
                'Expires' => static::httpDate($expires),
                'Cache-Control' => $cacheControl,
            ]);
        }

        $headers = [
            'Content-Type' => "$mimeType; charset=utf-8",
            'Expires' => static::httpDate($expires),
            'Cache-Control' => $cacheControl,
            'Last-Modified' => static::httpDate($lastModified),
        ];

        if (str($file)->endsWith('.br')) {
            $headers['Content-Encoding'] = 'br';
        }

        return response()->file($file, $headers);
    }

    static function matchesCache($lastModified)
    {
        $ifModifiedSince = $_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '';

        return @strtotime($ifModifiedSince) === $lastModified;
    }

    static function httpDate($timestamp)
    {
        return sprintf('%s GMT', gmdate('D, d M Y H:i:s', $timestamp));
    }
}
