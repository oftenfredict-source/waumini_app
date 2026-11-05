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
     * Inline script from resources/js (ships with the app; not dependent on /public deploy).
     */
    public static function inlineResourceJs(string $filename): string
    {
        $path = resource_path('js/'.ltrim($filename, '/'));

        if (! is_file($path)) {
            return '';
        }

        return '<script>'.file_get_contents($path).'</script>';
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

    public static function defaultColor(): string
    {
        return self::normalizeHex((string) config('waumini.brand_color', '#940000'));
    }

    public static function normalizeHex(?string $hex, ?string $fallback = null): string
    {
        foreach ([$hex, $fallback, '#940000'] as $candidate) {
            $value = strtoupper(ltrim(trim((string) $candidate), '#'));

            if (preg_match('/^[A-F0-9]{3}$/', $value) === 1) {
                $value = $value[0].$value[0].$value[1].$value[1].$value[2].$value[2];
            }

            if (preg_match('/^[A-F0-9]{6}$/', $value) === 1) {
                return '#'.$value;
            }
        }

        return '#940000';
    }

    /**
     * @return array{r: int, g: int, b: int}
     */
    public static function hexToRgb(string $hex): array
    {
        $hex = ltrim(self::normalizeHex($hex), '#');

        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }

    public static function shade(string $hex, float $factor): string
    {
        $rgb = self::hexToRgb($hex);
        $clamp = static fn (float $channel): int => max(0, min(255, (int) round($channel * $factor)));

        return sprintf('#%02X%02X%02X', $clamp($rgb['r']), $clamp($rgb['g']), $clamp($rgb['b']));
    }

    public static function mixWithWhite(string $hex, float $amount = 0.94): string
    {
        $amount = max(0, min(1, $amount));
        $rgb = self::hexToRgb($hex);
        $mix = static fn (int $channel): int => (int) round($channel + ((255 - $channel) * $amount));

        return sprintf('#%02X%02X%02X', $mix($rgb['r']), $mix($rgb['g']), $mix($rgb['b']));
    }

    /**
     * @return array{
     *     brand: string,
     *     hover: string,
     *     active: string,
     *     darkest: string,
     *     logo: string,
     *     soft: string,
     *     rgb: string
     * }
     */
    public static function palette(?string $hex = null): array
    {
        $brand = self::normalizeHex($hex);
        $rgb = self::hexToRgb($brand);

        return [
            'brand' => $brand,
            'hover' => self::shade($brand, 0.78),
            'active' => self::shade($brand, 0.65),
            'darkest' => self::shade($brand, 0.57),
            'logo' => self::shade($brand, 0.78),
            'soft' => self::mixWithWhite($brand, 0.94),
            'rgb' => "{$rgb['r']}, {$rgb['g']}, {$rgb['b']}",
        ];
    }
}
