<?php

namespace App\Helper;

class UrlHelper
{
    public static function extractAppleMusicID(string $url): int {
        $parsedUrl = parse_url($url);

        if (isset($parsedUrl['path'])) {
            $pathParts = explode('/', trim($parsedUrl['path'], '/'));

            if (count($pathParts) >= 4 && $pathParts[0] == 'us' && $pathParts[1] == 'artist') {
                $id = $pathParts[3];

                if (ctype_digit($id)) {
                    return (int)$id;
                }
            }
        }
        return 0;
    }
}
