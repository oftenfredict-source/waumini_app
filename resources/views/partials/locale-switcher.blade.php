@php
    $currentLocale = app()->getLocale();
    $locales = config('locales.supported', []);
    $variant = $variant ?? 'dropdown';
    $flagUrl = fn (string $code) => 'https://flagcdn.com/w40/'.strtolower($code).'.png';
@endphp

@if($variant === 'links')
    <div class="locale-switcher locale-switcher--flags {{ $class ?? '' }}" role="group" aria-label="{{ __('common.language') }}">
        @foreach($locales as $code => $meta)
            @php
                $label = $meta['native'] ?? $meta['name'] ?? strtoupper($code);
                $flag = $meta['flag'] ?? $code;
            @endphp
            <a href="{{ route('locale.switch', $code) }}"
               class="locale-switcher__flag {{ $currentLocale === $code ? 'is-active' : '' }}"
               hreflang="{{ $code }}"
               title="{{ $label }}"
               aria-label="{{ $label }}"
               @if($currentLocale === $code) aria-current="true" @endif>
                <img src="{{ $flagUrl($flag) }}" alt="" width="20" height="15" loading="lazy" decoding="async">
            </a>
        @endforeach
    </div>
@else
    <li class="dropdown locale-switcher">
        @php
            $currentMeta = $locales[$currentLocale] ?? [];
            $currentFlag = $currentMeta['flag'] ?? $currentLocale;
            $currentLabel = $currentMeta['native'] ?? $currentMeta['name'] ?? strtoupper($currentLocale);
        @endphp
        <a class="app-nav__item locale-switcher__trigger" href="#" data-toggle="dropdown" aria-label="{{ __('common.language') }}" title="{{ $currentLabel }}">
            <img src="{{ $flagUrl($currentFlag) }}" alt="" width="20" height="15" class="locale-switcher__trigger-flag" loading="lazy" decoding="async">
            <span class="caret"></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-right">
            @foreach($locales as $code => $meta)
                @php
                    $label = $meta['native'] ?? $meta['name'] ?? strtoupper($code);
                    $flag = $meta['flag'] ?? $code;
                @endphp
                <li>
                    <a class="dropdown-item locale-switcher__dropdown-item {{ $currentLocale === $code ? 'active font-weight-bold' : '' }}"
                       href="{{ route('locale.switch', $code) }}"
                       hreflang="{{ $code }}">
                        <img src="{{ $flagUrl($flag) }}" alt="" width="20" height="15" loading="lazy" decoding="async">
                        <span>{{ $label }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </li>
@endif
