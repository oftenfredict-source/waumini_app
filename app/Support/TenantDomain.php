<?php

namespace App\Support;

use App\Models\Church;

class TenantDomain
{
    public static function base(): string
    {
        return strtolower((string) config('waumini.base_domain'));
    }

    public static function forSlug(string $slug): string
    {
        return $slug.'.'.static::base();
    }

    public static function forChurch(Church $church): string
    {
        return static::forSlug($church->slug);
    }

    public static function url(string $domain, string $path = '/'): string
    {
        $scheme = parse_url((string) config('app.url'), PHP_URL_SCHEME) ?: 'https';
        $path = '/'.ltrim($path, '/');

        if ($path === '/') {
            return $scheme.'://'.$domain;
        }

        return $scheme.'://'.$domain.$path;
    }

    public static function churchUrl(Church $church, string $path = '/'): string
    {
        return static::url(static::forChurch($church), $path);
    }
}
