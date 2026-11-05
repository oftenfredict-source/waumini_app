@php
    $brandPalette = $brandPalette ?? \App\Support\WauminiBrand::palette();
@endphp
<meta name="theme-color" content="{{ $brandPalette['brand'] }}">
<meta name="brand-color" content="{{ $brandPalette['brand'] }}">
<link rel="stylesheet" href="{{ \App\Support\WauminiBrand::publicAsset('css/waumini-brand.css') }}?v=title-compact-3">
@include('partials.inline-resource-css', ['file' => 'waumini-mobile.css'])
<link rel="stylesheet" href="{{ \App\Support\WauminiBrand::publicAsset('css/waumini-mobile.css') }}?v=3">
<style>
    :root {
        --waumini-font: {!! config('waumini.font_family') !!};
        --font-family-sans-serif: var(--waumini-font);
        --primary: {{ $brandPalette['brand'] }};
        --waumini-brand: {{ $brandPalette['brand'] }};
        --waumini-brand-hover: {{ $brandPalette['hover'] }};
        --waumini-brand-active: {{ $brandPalette['active'] }};
        --waumini-brand-darkest: {{ $brandPalette['darkest'] }};
        --waumini-brand-logo: {{ $brandPalette['logo'] }};
        --waumini-brand-soft: {{ $brandPalette['soft'] }};
        --waumini-brand-rgb: {{ $brandPalette['rgb'] }};
    }
    .locale-switcher--flags {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }
    .locale-switcher__flag {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        border-radius: 999px;
        border: 1px solid rgba(0, 0, 0, 0.08);
        background: rgba(255, 255, 255, 0.85);
        text-decoration: none;
        opacity: 0.72;
        transition: opacity 0.2s, transform 0.2s, box-shadow 0.2s, border-color 0.2s;
    }
    .locale-switcher__flag img {
        display: block;
        width: 1.15rem;
        height: auto;
        border-radius: 2px;
        object-fit: cover;
    }
    .locale-switcher__flag:hover {
        opacity: 1;
        transform: translateY(-1px);
    }
    .locale-switcher__flag.is-active {
        opacity: 1;
        border-color: var(--waumini-brand, #940000);
        box-shadow: 0 0 0 2px rgba(var(--waumini-brand-rgb), 0.18);
    }
    .locale-switcher__trigger {
        display: inline-flex !important;
        align-items: center;
        gap: 0.35rem;
    }
    .locale-switcher__trigger-flag {
        display: block;
        border-radius: 2px;
        object-fit: cover;
    }
    .locale-switcher__dropdown-item {
        display: flex !important;
        align-items: center;
        gap: 0.55rem;
    }
    .locale-switcher__dropdown-item img {
        border-radius: 2px;
        object-fit: cover;
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
