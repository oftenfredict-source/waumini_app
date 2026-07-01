@php
    $brand = config('waumini.brand_color', '#940000');
    $appName = \App\Support\WauminiBrand::appDisplayName();
    $logoUrl = \App\Support\WauminiBrand::logoUrl();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('errors.default_title')) — {{ $appName }}</title>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" crossorigin="anonymous">
    @include('partials.brand-styles')
    @include('partials.inline-resource-css', ['file' => 'error-page.css'])
    <link rel="stylesheet" href="{{ \App\Support\WauminiBrand::publicAsset('css/error-page.css') }}?v=1">
    <style>:root { --error-brand: {{ $brand }}; }</style>
</head>
<body class="error-page">
    <header class="error-topbar">
        <div class="error-topbar-inner">
            <a href="{{ route('landing') }}" class="error-topbar-brand">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $appName }}">
                @else
                    <span>{{ $appName }}</span>
                @endif
            </a>
            <div class="error-topbar-actions">
                @include('partials.locale-switcher', ['variant' => 'links', 'class' => 'error-locale-switcher'])
                <a href="{{ route('landing') }}" class="error-topbar-link">{{ __('errors.back_to_home') }}</a>
            </div>
        </div>
    </header>

    <main class="error-main">
        <div class="error-shell">
            @yield('content')
        </div>
    </main>

    <footer class="error-page-footer">
        &copy; {{ date('Y') }} {{ $appName }}
    </footer>
</body>
</html>
