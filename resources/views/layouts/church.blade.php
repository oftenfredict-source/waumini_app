@php $vali = \App\Support\WauminiBrand::publicAsset('vali-master/docs'); @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('common.dashboard')) - {{ auth()->user()->church?->name ?? config('app.name') }}</title>
    <link rel="stylesheet" href="{{ $vali }}/css/main.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    @include('partials.brand-styles')
    @stack('styles')
</head>
<body class="app sidebar-mini">
    @include('church.partials.owner-impersonation-banner')
    <header class="app-header">
        @php $churchLogoUrl = auth()->user()->church?->logoUrl(); @endphp
        <a class="app-header__logo{{ $churchLogoUrl ? ' app-header__logo--brand-image' : '' }}"
           href="{{ auth()->user()->isChurchMember() ? route('church.member.dashboard') : route('church.dashboard') }}">
            @if($churchLogoUrl)
                <img src="{{ $churchLogoUrl }}" alt="{{ auth()->user()->church->name }}" class="app-header__brand-logo">
            @else
                {{ auth()->user()->church?->name ?? config('app.name') }}
            @endif
        </a>
        <a class="app-sidebar__toggle" href="#" data-toggle="sidebar" aria-label="{{ __('common.hide_sidebar') }}"></a>
        <ul class="app-nav">
            @include('church.partials.header-notifications')
            @include('partials.locale-switcher')
            <li class="dropdown">
                <a class="app-nav__item" href="#" data-toggle="dropdown">
                    <i class="fa fa-user fa-lg"></i>
                </a>
                <ul class="dropdown-menu settings-menu dropdown-menu-right">
                    <li><span class="dropdown-item-text">{{ auth()->user()->name }}</span></li>
                    <li>
                        <form action="{{ route('church.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="fa fa-sign-out fa-lg"></i> {{ __('common.logout') }}
                            </button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </header>

    <div class="app-sidebar__overlay" data-toggle="sidebar"></div>
    <aside class="app-sidebar">
        <div class="app-sidebar__user">
            @if(auth()->user()->member?->profilePictureUrl())
                <img class="app-sidebar__user-avatar" src="{{ auth()->user()->member->profilePictureUrl() }}" alt="{{ auth()->user()->name }}">
            @else
                <div class="app-sidebar__user-avatar app-sidebar__user-avatar--placeholder" aria-hidden="true">
                    <i class="fa fa-user"></i>
                </div>
            @endif
            <div>
                <p class="app-sidebar__user-name">{{ auth()->user()->name }}</p>
                <p class="app-sidebar__user-designation">{{ auth()->user()->churchRoleLabel() }}</p>
            </div>
        </div>
        @include('church.partials.sidebar-menu')
    </aside>

    <main class="app-content">
        @include('partials.sweetalert-flash')
        @yield('content')
    </main>

    <script src="{{ $vali }}/js/jquery-3.2.1.min.js"></script>
    <script src="{{ $vali }}/js/popper.min.js"></script>
    <script src="{{ $vali }}/js/bootstrap.min.js"></script>
    <script src="{{ $vali }}/js/main.js"></script>
    @include('partials.sweetalert-assets')
    @include('partials.mobile-admin')
    @stack('scripts')
</body>
</html>
