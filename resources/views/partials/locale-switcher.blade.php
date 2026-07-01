@php
    $currentLocale = app()->getLocale();
    $locales = config('locales.supported', []);
    $variant = $variant ?? 'dropdown';
@endphp

@if($variant === 'links')
    <div class="locale-switcher locale-switcher--links {{ $class ?? '' }}">
        @foreach($locales as $code => $meta)
            <a href="{{ route('locale.switch', $code) }}"
               class="locale-switcher__link {{ $currentLocale === $code ? 'is-active' : '' }}"
               hreflang="{{ $code }}">
                {{ $meta['native'] ?? strtoupper($code) }}
            </a>
            @if(! $loop->last)
                <span class="locale-switcher__sep" aria-hidden="true">|</span>
            @endif
        @endforeach
    </div>
@else
    <li class="dropdown locale-switcher">
        <a class="app-nav__item" href="#" data-toggle="dropdown" aria-label="{{ __('common.language') }}">
            <i class="fa fa-globe fa-lg"></i>
        </a>
        <ul class="dropdown-menu dropdown-menu-right">
            @foreach($locales as $code => $meta)
                <li>
                    <a class="dropdown-item {{ $currentLocale === $code ? 'active font-weight-bold' : '' }}"
                       href="{{ route('locale.switch', $code) }}"
                       hreflang="{{ $code }}">
                        {{ $meta['native'] ?? $meta['name'] ?? strtoupper($code) }}
                    </a>
                </li>
            @endforeach
        </ul>
    </li>
@endif
