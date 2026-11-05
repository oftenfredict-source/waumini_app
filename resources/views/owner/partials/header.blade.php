@php
    $ownerLogoUrl = \App\Support\WauminiBrand::logoUrl();
    $ownerAppName = \App\Support\WauminiBrand::appDisplayName();
    $currentLocale = app()->getLocale();
    $locales = config('locales.supported', []);
    $flagUrl = fn (string $code) => 'https://flagcdn.com/w40/'.strtolower($code).'.png';
    $currentMeta = $locales[$currentLocale] ?? [];
    $currentFlag = $currentMeta['flag'] ?? $currentLocale;
    $currentLabel = $currentMeta['native'] ?? $currentMeta['name'] ?? strtoupper($currentLocale);
@endphp

<header class="admin-header">
    <div class="admin-header-inner">
        <div class="admin-header-rail">
            <a class="admin-header-brand" href="{{ route('owner.dashboard') }}" title="{{ $ownerAppName }}">
                @if($ownerLogoUrl)
                    <img src="{{ $ownerLogoUrl }}" alt="{{ $ownerAppName }}">
                @else
                    <span class="admin-header-brand-text">{{ $ownerAppName }}</span>
                @endif
            </a>
        </div>

        <button class="admin-header-toggle" type="button" data-sidebar-toggle aria-label="{{ __('common.hide_sidebar') }}" aria-controls="admin-sidebar">
            <i class="bi bi-list"></i>
        </button>

        <div class="admin-header-main">
            <div class="admin-header-tools">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm d-flex align-items-center"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            title="{{ $currentLabel }}">
                        <img src="{{ $flagUrl($currentFlag) }}" alt="" width="18" height="14" class="me-1">
                        <span class="d-none d-md-inline">{{ strtoupper($currentLocale) }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        @foreach($locales as $code => $meta)
                            @php
                                $label = $meta['native'] ?? $meta['name'] ?? strtoupper($code);
                                $flag = $meta['flag'] ?? $code;
                            @endphp
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 {{ $currentLocale === $code ? 'active' : '' }}"
                                   href="{{ route('locale.switch', $code) }}">
                                    <img src="{{ $flagUrl($flag) }}" alt="" width="18" height="14">
                                    <span>{{ $label }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm d-flex align-items-center"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">
                        <i class="bi bi-person-circle me-1"></i>
                        <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
                        <i class="bi bi-chevron-down ms-1 small"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">{{ __('owner.super_admin') }}</h6></li>
                        <li>
                            <form action="{{ route('owner.logout') }}" method="POST" class="px-3 py-1">
                                @csrf
                                <button type="submit" class="dropdown-item px-0">
                                    <i class="bi bi-box-arrow-right me-2"></i> {{ __('common.logout') }}
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>
