@php $vali = \App\Support\WauminiBrand::publicAsset('vali-master/docs'); @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Owner Dashboard') - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ $vali }}/css/main.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    @include('partials.brand-styles')
    @stack('styles')
</head>
<body class="app sidebar-mini">
    <header class="app-header">
        <a class="app-header__logo" href="{{ route('owner.dashboard') }}">{{ config('app.name') }}</a>
        <a class="app-sidebar__toggle" href="#" data-toggle="sidebar" aria-label="Hide Sidebar"></a>
        <ul class="app-nav">
            <li class="dropdown">
                <a class="app-nav__item" href="#" data-toggle="dropdown">
                    <i class="fa fa-user fa-lg"></i>
                </a>
                <ul class="dropdown-menu settings-menu dropdown-menu-right">
                    <li><span class="dropdown-item-text">{{ auth()->user()->name }}</span></li>
                    <li>
                        <form action="{{ route('owner.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="fa fa-sign-out fa-lg"></i> Logout
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
            <img class="app-sidebar__user-avatar" src="https://s3.amazonaws.com/uifaces/faces/twitter/jsa/48.jpg" alt="User">
            <div>
                <p class="app-sidebar__user-name">{{ auth()->user()->name }}</p>
                <p class="app-sidebar__user-designation">Super Admin</p>
            </div>
        </div>
        <ul class="app-menu">
            <li>
                <a class="app-menu__item @if(request()->routeIs('owner.dashboard')) active @endif" href="{{ route('owner.dashboard') }}">
                    <i class="app-menu__icon fa fa-dashboard"></i>
                    <span class="app-menu__label">Overview</span>
                </a>
            </li>
            <li>
                <a class="app-menu__item @if(request()->routeIs('owner.churches.*')) active @endif" href="{{ route('owner.churches.index') }}">
                    <i class="app-menu__icon fa fa-building"></i>
                    <span class="app-menu__label">Churches</span>
                </a>
            </li>
            <li>
                <a class="app-menu__item @if(request()->routeIs('owner.subscriptions.*')) active @endif" href="{{ route('owner.subscriptions.index') }}">
                    <i class="app-menu__icon fa fa-credit-card"></i>
                    <span class="app-menu__label">Subscriptions</span>
                </a>
            </li>
            <li>
                <a class="app-menu__item @if(request()->routeIs('owner.payments.*')) active @endif" href="{{ route('owner.payments.index') }}">
                    <i class="app-menu__icon fa fa-money"></i>
                    <span class="app-menu__label">Payments</span>
                </a>
            </li>
            <li>
                <a class="app-menu__item @if(request()->routeIs('owner.revenue.*')) active @endif" href="{{ route('owner.revenue.index') }}">
                    <i class="app-menu__icon fa fa-bar-chart"></i>
                    <span class="app-menu__label">Revenue</span>
                </a>
            </li>
            <li>
                <a class="app-menu__item @if(request()->routeIs('owner.users.*')) active @endif" href="{{ route('owner.users.index') }}">
                    <i class="app-menu__icon fa fa-users"></i>
                    <span class="app-menu__label">Users & Roles</span>
                </a>
            </li>
            <li>
                <a class="app-menu__item @if(request()->routeIs('owner.support.*')) active @endif" href="{{ route('owner.support.index') }}">
                    <i class="app-menu__icon fa fa-life-ring"></i>
                    <span class="app-menu__label">Support</span>
                </a>
            </li>
            <li>
                <a class="app-menu__item @if(request()->routeIs('owner.settings.*')) active @endif" href="{{ route('owner.settings.index') }}">
                    <i class="app-menu__icon fa fa-cog"></i>
                    <span class="app-menu__label">Settings</span>
                </a>
            </li>
        </ul>
    </aside>

    <main class="app-content">
        @include('partials.sweetalert-flash')
        @yield('content')
    </main>

    <script src="{{ $vali }}/js/jquery-3.2.1.min.js"></script>
    <script src="{{ $vali }}/js/popper.min.js"></script>
    <script src="{{ $vali }}/js/bootstrap.min.js"></script>
    <script src="{{ $vali }}/js/main.js"></script>
    <script src="{{ $vali }}/js/plugins/pace.min.js"></script>
    @include('partials.sweetalert-assets')
    @include('partials.mobile-admin')
    @stack('scripts')
</body>
</html>
