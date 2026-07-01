@php
    $s = fn (string $path) => \App\Support\WauminiBrand::publicAsset('skilline/'.$path);
    $brand = config('waumini.brand_color', '#940000');
    $logoUrl = \App\Support\WauminiBrand::logoUrl();
    $appName = \App\Support\WauminiBrand::appDisplayName();
    $landingCss = \App\Support\WauminiBrand::publicAsset('css/landing.css');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $appName }} — {{ __('landing.tagline') }}</title>
    <meta name="description" content="{{ $appName }} {{ __('landing.meta_description') }}">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{ $landingCss }}?v=15">
    <link rel="icon" href="{{ $logoUrl ?? \App\Support\WauminiBrand::publicAsset('waumini_link_logo.png') }}" type="image/png">
    <style>
        :root { --brand: {{ $brand }}; --brand-dark: {{ $brand }}; --nav-height: 4.5rem; }
        html, body.landing-page, .landing-shell {
            overflow-x: hidden !important;
            max-width: 100% !important;
            width: 100% !important;
        }
        .landing-shell { padding-top: var(--nav-height); }
        .landing-nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            z-index: 50;
            background: rgba(255, 255, 255, 0.92);
            border-bottom: 1px solid rgba(26, 29, 46, 0.06);
        }
        @media (max-width: 991.98px) {
            .landing-nav-actions { display: flex !important; }
            .landing-nav-actions .landing-hide-mobile { display: none !important; }
            .landing-hero-grid, .landing-about, .landing-container { min-width: 0; max-width: 100%; }
            .landing-page img:not(.landing-brand-logo):not(.landing-footer-brand-logo) { max-width: 100% !important; height: auto; }
        }
        .landing-page img.landing-brand-logo,
        .landing-page img.landing-footer-brand-logo {
            height: 2.75rem;
            width: auto;
            max-width: 11rem;
            object-fit: contain;
        }
        .landing-page img.landing-footer-brand-logo { height: 3.25rem; max-width: 13rem; }
    </style>
</head>
<body class="landing-page" x-data="{ open: false }">
<div class="landing-shell">

    <header class="landing-nav">
        <div class="landing-container landing-nav-wrap">
            <div class="landing-nav-inner">
                <a href="#home" class="landing-brand">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="landing-brand-logo">
                    @else
                        <span class="landing-brand-mark"><i class="fa fa-link"></i></span>
                        <span>{{ $appName }}</span>
                    @endif
                </a>

                <button type="button" class="landing-menu-toggle landing-hide-desktop" @click="open = !open" aria-label="{{ __('landing.toggle_menu') }}">
                    <i class="fa" :class="open ? 'fa-times' : 'fa-bars'"></i>
                </button>

                <nav class="landing-menu landing-nav-center" :class="{ 'is-open': open }">
                    <a href="#home" @click="open = false">{{ __('landing.home') }}</a>
                    <a href="#features" @click="open = false">{{ __('landing.features') }}</a>
                    <a href="#about" @click="open = false">{{ __('landing.about') }}</a>
                    <a href="#pricing" @click="open = false">{{ __('landing.pricing') }}</a>
                </nav>

                <div class="landing-nav-actions">
                    @include('partials.locale-switcher', ['variant' => 'links', 'class' => 'landing-locale-switcher landing-hide-mobile'])
                    <a href="{{ route('church.login') }}" class="landing-btn landing-btn-ghost landing-hide-mobile">{{ __('landing.church_login') }}</a>
                    <a href="{{ route('church.login') }}" class="landing-btn landing-btn-primary">{{ __('landing.get_started') }}</a>
                </div>
            </div>
        </div>
    </header>

    <section id="home" class="landing-hero">
        <div class="landing-hero-deco one" aria-hidden="true"></div>
        <div class="landing-hero-deco two" aria-hidden="true"></div>

        <div class="landing-container landing-hero-grid">
            <div>
                <div class="landing-eyebrow">
                    <i class="fa fa-shield"></i> {{ __('landing.trusted_platform') }}
                </div>
                <h1>{!! __('landing.hero_title') !!}</h1>
                <p class="landing-hero-lead">
                    {{ __('landing.hero_lead', ['app' => $appName]) }}
                </p>
                <div class="landing-hero-actions">
                    <a href="{{ route('church.login') }}" class="landing-btn landing-btn-primary">
                        <i class="fa fa-sign-in"></i> {{ __('landing.sign_in_church') }}
                    </a>
                    <a href="#pricing" class="landing-btn landing-btn-ghost">
                        <i class="fa fa-tags"></i> {{ __('landing.view_pricing') }}
                    </a>
                </div>
                <div class="landing-stats">
                    <div class="landing-stat">
                        <strong>{{ __('landing.stat_all_in_one') }}</strong>
                        <span>{{ __('landing.stat_all_in_one_desc') }}</span>
                    </div>
                    <div class="landing-stat">
                        <strong>{{ __('landing.stat_secure') }}</strong>
                        <span>{{ __('landing.stat_secure_desc') }}</span>
                    </div>
                    <div class="landing-stat">
                        <strong>{{ __('landing.stat_cloud') }}</strong>
                        <span>{{ __('landing.stat_cloud_desc') }}</span>
                    </div>
                </div>
            </div>

            <div class="landing-hero-visual">
                <div class="landing-hero-badge calendar">
                    <img src="{{ $s('img/calendar.svg') }}" alt="">
                </div>
                <img src="{{ $s('img/girl-laptop.png') }}" alt="{{ $appName }} dashboard preview">
                <div class="landing-float-card one">
                    <strong><i class="fa fa-calendar-check-o landing-icon-accent"></i> {{ __('landing.float_attendance') }}</strong>
                    <span>{{ __('landing.float_attendance_desc') }}</span>
                </div>
                <div class="landing-float-card two">
                    <strong><i class="fa fa-money landing-icon-accent"></i> {{ __('landing.float_giving') }}</strong>
                    <span>{{ __('landing.float_giving_desc') }}</span>
                </div>
            </div>
        </div>
        <svg class="landing-wave" viewBox="0 0 1200 120" preserveAspectRatio="none" aria-hidden="true">
            <path d="M600,112.77C268.63,112.77,0,65.52,0,7.23V120H1200V7.23C1200,65.52,931.37,112.77,600,112.77Z" fill="currentColor"></path>
        </svg>
    </section>

    <section class="landing-section" id="features">
        <div class="landing-container">
            <div class="landing-pill-row">
                <span class="landing-pill"><i class="fa fa-users"></i> {{ __('landing.pill_members') }}</span>
                <span class="landing-pill"><i class="fa fa-money"></i> {{ __('landing.pill_finance') }}</span>
                <span class="landing-pill"><i class="fa fa-calendar"></i> {{ __('landing.pill_attendance') }}</span>
                <span class="landing-pill"><i class="fa fa-envelope"></i> {{ __('landing.pill_sms') }}</span>
                <span class="landing-pill"><i class="fa fa-line-chart"></i> {{ __('landing.pill_reports') }}</span>
            </div>

            <div class="landing-section-head">
                <span class="eyebrow">{{ __('landing.features_eyebrow') }}</span>
                <h2>{{ __('landing.features_title') }}</h2>
                <p>{{ __('landing.features_lead') }}</p>
            </div>

            <div class="landing-feature-grid">
                <article class="landing-feature-card">
                    <div class="landing-feature-icon" style="background: linear-gradient(135deg, #5b72ee, #7b8cff);">
                        <i class="fa fa-users"></i>
                    </div>
                    <h3>{{ __('landing.feature_members_title') }}</h3>
                    <p>{{ __('landing.feature_members_desc') }}</p>
                </article>
                <article class="landing-feature-card">
                    <div class="landing-feature-icon" style="background: linear-gradient(135deg, #940000, #c41e1e);">
                        <i class="fa fa-money"></i>
                    </div>
                    <h3>{{ __('landing.feature_finance_title') }}</h3>
                    <p>{{ __('landing.feature_finance_desc') }}</p>
                </article>
                <article class="landing-feature-card">
                    <div class="landing-feature-icon" style="background: linear-gradient(135deg, #29b9e7, #5ed0f7);">
                        <i class="fa fa-calendar-check-o"></i>
                    </div>
                    <h3>{{ __('landing.feature_services_title') }}</h3>
                    <p>{{ __('landing.feature_services_desc') }}</p>
                </article>
                <article class="landing-feature-card">
                    <div class="landing-feature-icon" style="background: linear-gradient(135deg, #f48c06, #ffb347);">
                        <i class="fa fa-bullhorn"></i>
                    </div>
                    <h3>{{ __('landing.feature_announcements_title') }}</h3>
                    <p>{{ __('landing.feature_announcements_desc') }}</p>
                </article>
                <article class="landing-feature-card">
                    <div class="landing-feature-icon" style="background: linear-gradient(135deg, #33c18f, #5fe0ad);">
                        <i class="fa fa-sitemap"></i>
                    </div>
                    <h3>{{ __('landing.feature_branches_title') }}</h3>
                    <p>{{ __('landing.feature_branches_desc') }}</p>
                </article>
                <article class="landing-feature-card">
                    <div class="landing-feature-icon" style="background: linear-gradient(135deg, #2f327d, #4b4f9b);">
                        <i class="fa fa-bar-chart"></i>
                    </div>
                    <h3>{{ __('landing.feature_reports_title') }}</h3>
                    <p>{{ __('landing.feature_reports_desc') }}</p>
                </article>
            </div>
        </div>
    </section>

    <section class="landing-section alt" id="about">
        <div class="landing-container landing-about">
            <div class="landing-about-copy">
                <span class="eyebrow">{{ __('landing.about_eyebrow', ['app' => $appName]) }}</span>
                <h2>{{ __('landing.about_title') }}</h2>
                <p class="landing-about-lead">
                    {{ __('landing.about_lead', ['app' => $appName]) }}
                </p>
                <ul class="landing-checklist">
                    <li><i class="fa fa-check-circle"></i><span>{{ __('landing.about_check_1') }}</span></li>
                    <li><i class="fa fa-check-circle"></i><span>{{ __('landing.about_check_2') }}</span></li>
                    <li><i class="fa fa-check-circle"></i><span>{{ __('landing.about_check_3') }}</span></li>
                    <li><i class="fa fa-check-circle"></i><span>{{ __('landing.about_check_4') }}</span></li>
                </ul>

                <div class="landing-about-audience">
                    <div class="landing-audience-card">
                        <div class="landing-audience-icon"><i class="fa fa-users"></i></div>
                        <div>
                            <h4>{{ __('landing.audience_leaders') }}</h4>
                            <p>{{ __('landing.audience_leaders_desc') }}</p>
                            <a href="{{ route('church.login') }}">{{ __('landing.open_dashboard') }} <i class="fa fa-arrow-right"></i></a>
                        </div>
                    </div>
                    <div class="landing-audience-card">
                        <div class="landing-audience-icon alt"><i class="fa fa-user"></i></div>
                        <div>
                            <h4>{{ __('landing.audience_members') }}</h4>
                            <p>{{ __('landing.audience_members_desc') }}</p>
                            <a href="{{ route('church.login') }}">{{ __('landing.member_login') }} <i class="fa fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="landing-about-visual">
                <div class="landing-about-frame">
                    <img src="{{ $s('img/teacher-explaining.png') }}" alt="{{ __('landing.about_image_alt', ['app' => $appName]) }}">
                </div>
                <div class="landing-about-badge">
                    <i class="fa fa-cloud"></i>
                    <div>
                        <strong>{{ __('landing.cloud_based') }}</strong>
                        <span>{{ __('landing.cloud_desc') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-section">
        <div class="landing-container">
            <div class="landing-section-head">
                <span class="eyebrow">{{ __('landing.how_eyebrow') }}</span>
                <h2>{{ __('landing.how_title') }}</h2>
                <p>{{ __('landing.how_lead') }}</p>
            </div>
            <div class="landing-steps">
                <div class="landing-step">
                    <div class="landing-step-num">1</div>
                    <div>
                        <h3>{{ __('landing.step_1_title') }}</h3>
                        <p>{{ __('landing.step_1_desc') }}</p>
                    </div>
                </div>
                <div class="landing-step">
                    <div class="landing-step-num">2</div>
                    <div>
                        <h3>{{ __('landing.step_2_title') }}</h3>
                        <p>{{ __('landing.step_2_desc') }}</p>
                    </div>
                </div>
                <div class="landing-step">
                    <div class="landing-step-num">3</div>
                    <div>
                        <h3>{{ __('landing.step_3_title') }}</h3>
                        <p>{{ __('landing.step_3_desc') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-section alt" id="pricing">
        <div class="landing-container">
            <div class="landing-section-head">
                <span class="eyebrow">{{ __('landing.pricing_eyebrow') }}</span>
                <h2>{{ __('landing.pricing_title') }}</h2>
                <p>{{ str_replace(':trial', $packages->isNotEmpty() ? __('landing.pricing_lead_trial') : '', __('landing.pricing_lead')) }}</p>
            </div>

            <div class="landing-pricing-grid">
                @forelse($packages as $package)
                    @php
                        $isPopular = $packages->count() > 1 && $loop->index === (int) floor($packages->count() / 2);
                    @endphp
                    <article class="landing-price-card {{ $isPopular ? 'popular' : '' }}">
                        @if($isPopular)
                            <span class="landing-price-badge">{{ __('landing.most_popular') }}</span>
                        @endif
                        <h3>{{ $package->name }}</h3>
                        <p class="desc">{{ $package->description }}</p>
                        @include('partials.public-package-pricing', ['package' => $package, 'currencyCode' => $currencyCode])
                        <ul class="landing-price-features">
                            @if($package->max_members)
                                <li><i class="fa fa-check"></i><span>{{ __('landing.up_to_members', ['count' => number_format($package->max_members)]) }}</span></li>
                            @else
                                <li><i class="fa fa-check"></i><span>{{ __('landing.unlimited_members') }}</span></li>
                            @endif
                            @if($package->max_sms_monthly)
                                <li><i class="fa fa-check"></i><span>{{ __('landing.sms_per_month', ['count' => number_format($package->max_sms_monthly)]) }}</span></li>
                            @endif
                            @foreach($package->features->filter(fn ($feature) => (bool) $feature->pivot?->is_enabled) as $feature)
                                <li><i class="fa fa-check"></i><span>{{ $feature->name }}</span></li>
                            @endforeach
                        </ul>
                        <a href="{{ route('church.login') }}" class="landing-btn {{ $isPopular ? 'landing-btn-primary' : 'landing-btn-ghost' }}">
                            {{ __('landing.get_started') }}
                        </a>
                    </article>
                @empty
                    <div class="landing-pricing-empty">
                        <p>{{ __('landing.pricing_empty') }}</p>
                        <a href="{{ route('church.login') }}" class="landing-btn landing-btn-primary">{{ __('landing.contact_get_started') }}</a>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="landing-section">
        <div class="landing-container">
            <div class="landing-cta">
                <h2>{{ __('landing.cta_title') }}</h2>
                <p>{{ __('landing.cta_lead', ['app' => $appName]) }}</p>
                <div class="landing-cta-actions">
                    <a href="{{ route('church.login') }}" class="landing-btn landing-btn-primary landing-cta-btn-light">
                        <i class="fa fa-rocket"></i> {{ __('landing.get_started_today') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    <footer class="landing-footer">
        <div class="landing-footer-top">
            <div class="landing-container landing-footer-grid">
                <div class="landing-footer-brand">
                    <a href="#home" class="landing-footer-logo">
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="landing-brand-logo landing-footer-brand-logo">
                        @else
                            <span>{{ $appName }}</span>
                        @endif
                    </a>
                    <p>{{ __('landing.footer_desc') }}</p>
                    <div class="landing-footer-highlights">
                        <span><i class="fa fa-shield"></i> {{ __('landing.footer_secure') }}</span>
                        <span><i class="fa fa-users"></i> {{ __('landing.footer_multibranch') }}</span>
                        <span><i class="fa fa-mobile"></i> {{ __('landing.footer_access') }}</span>
                    </div>
                </div>

                <div class="landing-footer-col">
                    <h4>{{ __('landing.footer_explore') }}</h4>
                    <ul>
                        <li><a href="#home">{{ __('landing.home') }}</a></li>
                        <li><a href="#features">{{ __('landing.features') }}</a></li>
                        <li><a href="#about">{{ __('landing.about') }}</a></li>
                        <li><a href="#pricing">{{ __('landing.pricing') }}</a></li>
                    </ul>
                </div>

                <div class="landing-footer-col">
                    <h4>{{ __('landing.footer_access_col') }}</h4>
                    <ul>
                        <li><a href="{{ route('church.login') }}">{{ __('landing.church_login') }}</a></li>
                        <li><a href="{{ route('owner.login') }}">{{ __('landing.platform_admin') }}</a></li>
                        <li><a href="{{ route('church.login') }}">{{ __('landing.get_started') }}</a></li>
                    </ul>
                </div>

                <div class="landing-footer-col landing-footer-contact-col">
                    <h4>{{ __('landing.footer_contact') }}</h4>
                    <ul class="landing-footer-contact">
                        <li>
                            <i class="fa fa-map-marker"></i>
                            <span>
                                Ben Bella Street, Moshi Municipality<br>
                                Opposite High Court of Tanzania<br>
                                P.O. Box 20, Moshi – Kilimanjaro<br>
                                Postcode: 25101
                            </span>
                        </li>
                        <li>
                            <i class="fa fa-phone"></i>
                            <span>
                                <span class="landing-contact-label">{{ __('landing.call_us') }}</span>
                                <a href="tel:+255749719998">+255 749 719 998</a>
                            </span>
                        </li>
                        <li>
                            <i class="fa fa-envelope-o"></i>
                            <span>
                                <span class="landing-contact-label">{{ __('landing.mail_us') }}</span>
                                <a href="mailto:emca@emca.tech">emca@emca.tech</a>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="landing-footer-bottom">
            <div class="landing-container landing-footer-bottom-inner">
                <p>&copy; {{ date('Y') }} {{ $appName }}. {{ __('landing.all_rights') }}</p>
                <p class="landing-footer-powered">{{ __('landing.powered_by') }} <a href="https://www.emca.tech" target="_blank" rel="noopener noreferrer">EmCa Technologies</a></p>
            </div>
        </div>
    </footer>

</div>

    <script type="module" src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js"></script>
</body>
</html>
