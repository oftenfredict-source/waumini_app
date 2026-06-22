@php
    $brand = config('waumini.brand_color', '#940000');
    $appName = \App\Support\WauminiBrand::appDisplayName();
    $logoUrl = \App\Support\WauminiBrand::logoUrl();
    $churchName = $church?->name ?? ($churchName ?? null);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Member Registration') - {{ $appName }}</title>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    @include('partials.brand-styles')
    <link rel="stylesheet" href="{{ asset('css/member-register.css') }}?v=2">
    <style>:root { --register-brand: {{ $brand }}; }</style>
    @stack('styles')
</head>
<body class="register-portal-page">
    <header class="register-topbar">
        <div class="register-topbar-inner">
            <a href="{{ route('church.login') }}" class="register-topbar-brand">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $appName }}">
                @else
                    <span>{{ $appName }}</span>
                @endif
            </a>
            @if($churchName)
                <span class="register-topbar-church"><i class="fa fa-building-o"></i> {{ $churchName }}</span>
            @endif
            <a href="{{ route('church.login') }}" class="register-topbar-login">Sign in</a>
        </div>
    </header>

    <main class="register-main">
        <div class="register-wrap">
            @yield('content')
        </div>
    </main>

    <footer class="register-footer">
        &copy; {{ date('Y') }} {{ $appName }}
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    @include('partials.sweetalert-assets')
    @stack('scripts')
</body>
</html>
