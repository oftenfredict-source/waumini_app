@php
    $brand = config('waumini.brand_color', '#940000');
    $appName = \App\Support\WauminiBrand::appDisplayName();
    $logoUrl = \App\Support\WauminiBrand::logoUrl();
    $homeUrl = $homeUrl ?? route('landing');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sign In') — {{ $appName }}</title>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    @include('partials.brand-styles')
    <link rel="stylesheet" href="{{ asset('css/auth-login.css') }}?v=1">
    <style>:root { --auth-brand: {{ $brand }}; }</style>
    @stack('styles')
</head>
<body class="auth-portal-page">
    <header class="auth-topbar">
        <div class="auth-topbar-inner">
            <a href="{{ $homeUrl }}" class="auth-topbar-brand">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $appName }}">
                @else
                    <span>{{ $appName }}</span>
                @endif
            </a>
            @hasSection('topbar_action')
                @yield('topbar_action')
            @endif
        </div>
    </header>

    <main class="auth-main">
        <div class="auth-shell">
            <div class="auth-panel">
                <div class="auth-eyebrow">
                    <i class="fa @yield('panel_icon', 'fa-shield')"></i>
                    @yield('panel_eyebrow', $appName)
                </div>
                <h1>@yield('panel_title')</h1>
                <p class="auth-panel-lead">@yield('panel_lead')</p>
                @hasSection('panel_features')
                    <div class="auth-features">
                        @yield('panel_features')
                    </div>
                @endif
            </div>

            <div class="auth-card">
                <div class="auth-card-head">
                    <h2>@yield('form_title')</h2>
                    @hasSection('form_subtitle')
                        <p>@yield('form_subtitle')</p>
                    @endif
                </div>

                @yield('content')

                @hasSection('auth_footer')
                    <div class="auth-footer-links">
                        @yield('auth_footer')
                    </div>
                @endif
            </div>
        </div>
    </main>

    <footer class="auth-page-footer">
        &copy; {{ date('Y') }} {{ $appName }}
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    @include('partials.sweetalert-assets')
    @stack('scripts')
</body>
</html>
