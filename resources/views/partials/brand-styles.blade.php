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
</style>
