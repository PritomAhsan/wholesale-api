<?php

namespace App\Support;

class MediaUrl
{
    /**
     * Stored image paths are usually relative (local uploads under the
     * public disk), but seed/demo data may store a full external URL
     * directly. Pass either through to the right place instead of
     * mangling an absolute URL by prepending the storage path to it.
     */
    public static function resolve(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/' . $path);
    }
}
