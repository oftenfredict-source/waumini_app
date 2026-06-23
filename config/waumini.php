<?php

return [
    'base_domain' => (function (): string {
        $configured = env('TENANT_BASE_DOMAIN');

        if (is_string($configured) && $configured !== '') {
            return strtolower($configured);
        }

        $host = parse_url((string) env('APP_URL', ''), PHP_URL_HOST);

        if (is_string($host) && $host !== '' && ! in_array($host, ['localhost', '127.0.0.1'], true)) {
            return strtolower((string) preg_replace('/^www\./i', '', $host));
        }

        return 'wauminilink.test';
    })(),
    'brand_color' => env('BRAND_COLOR', '#940000'),
    'logo' => env('APP_LOGO', 'waumini_link_logo.png'),
    'font_family' => env('APP_FONT_FAMILY', '"Century Gothic", CenturyGothic, "Apple SD Gothic Neo", "Trebuchet MS", "Segoe UI", Roboto, sans-serif'),
    'member_id_suffix' => env('MEMBER_ID_SUFFIX', 'WL'),
];
