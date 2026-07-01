<meta name="theme-color" content="{{ config('waumini.brand_color') }}">
<meta name="brand-color" content="{{ config('waumini.brand_color') }}">
<link rel="stylesheet" href="{{ \App\Support\WauminiBrand::publicAsset('css/waumini-brand.css') }}">
@include('partials.inline-resource-css', ['file' => 'waumini-mobile.css'])
<link rel="stylesheet" href="{{ \App\Support\WauminiBrand::publicAsset('css/waumini-mobile.css') }}?v=2">
<style>
    :root {
        --waumini-font: {!! config('waumini.font_family') !!};
        --font-family-sans-serif: var(--waumini-font);
    }
    .locale-switcher--links {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.875rem;
    }
    .locale-switcher__link {
        color: inherit;
        text-decoration: none;
        opacity: 0.75;
    }
    .locale-switcher__link:hover,
    .locale-switcher__link.is-active {
        opacity: 1;
        font-weight: 600;
        text-decoration: underline;
    }
    .locale-switcher__sep {
        opacity: 0.45;
    }
    .auth-topbar-actions,
    .register-topbar-actions {
        display: inline-flex;
        align-items: center;
        gap: 1rem;
        margin-left: auto;
    }
    .register-topbar-inner,
    .auth-topbar-inner {
        display: flex;
        align-items: center;
        gap: 1rem;
        width: 100%;
    }
</style>
