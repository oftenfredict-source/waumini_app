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
     * Absolute URL for a file in /public (respects URL::forceRootUrl and subfolder deploys).
     */
    public static function publicAsset(string $path): string
    {
        return url('/'.ltrim($path, '/'));
    }

    /**
     * Inline stylesheet from resources/css (ships with the app; not dependent on /public deploy).
     */
    public static function inlineResourceCss(string $filename): string
    {
        $path = resource_path('css/'.ltrim($filename, '/'));

        if (! is_file($path)) {
            return '';
        }

        return '<style>'.file_get_contents($path).'</style>';
    }

    /**
     * Public URL for the configured app logo.
     *
     * Does not require the file to exist on the PHP filesystem — production
     * deploys sometimes serve /public assets from a different document root.
     */
    public static function logoUrl(): ?string
    {
        $path = self::logoPath();

        if ($path === '') {
            return null;
        }

        return self::publicAsset($path);
    }

    public static function appDisplayName(): string
    {
        $name = (string) config('app.name');

        return $name === 'Laravel' ? 'Waumini Link' : $name;
    }
}
