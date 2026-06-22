<?php

namespace App\Support;

class WauminiBrand
{
    public static function logoPath(): string
    {
        return ltrim((string) config('waumini.logo', 'waumini_link_logo.png'), '/');
    }

    public static function logoAbsolutePath(): ?string
    {
        $path = public_path(self::logoPath());

        return is_file($path) ? $path : null;
    }

    /**
     * Logo URL relative to the current request base (works across host/port/subfolder).
     */
    public static function logoUrl(): ?string
    {
        if (! self::logoAbsolutePath()) {
            return null;
        }

        $base = rtrim(request()->getBaseUrl(), '/');

        return $base.'/'.self::logoPath();
    }

    public static function appDisplayName(): string
    {
        $name = (string) config('app.name');

        return $name === 'Laravel' ? 'Waumini Link' : $name;
    }
}
