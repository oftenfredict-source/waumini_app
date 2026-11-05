<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#940000">
    <title>@yield('title', __('owner.dashboard')) - {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    {{-- Inline from resources: live public/css/owner-metis.css currently 404s on deploy. --}}
    @include('partials.inline-resource-css', ['file' => 'owner-metis.css'])
    @include('partials.inline-resource-css', ['file' => 'page-manual.css'])
    @stack('styles')
</head>
<body data-page="owner" class="admin-layout">
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <div class="admin-app">
        <div class="admin-wrapper" id="admin-wrapper">
            @include('owner.partials.header')
            @include('owner.partials.sidebar')

            <main id="main-content" class="admin-main">
                <div class="container-fluid">
                    @include('partials.sweetalert-flash')
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @auth
        @include('partials.page-manual')
    @endauth

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    {!! \App\Support\WauminiBrand::inlineResourceJs('owner-metis.js') !!}
    @include('partials.sweetalert-assets')
    @stack('scripts')
</body>
</html>
