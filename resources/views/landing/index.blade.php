@php
    $brand = config('waumini.brand_color', '#940000');
    $logoUrl = \App\Support\WauminiBrand::logoUrl();
    $appName = \App\Support\WauminiBrand::appDisplayName();
    $landingJs = \App\Support\WauminiBrand::publicAsset('js/landing.js');
    $landingJsVersion = @filemtime(public_path('js/landing.js')) ?: time();

    $features = [
        [
            'id' => 'members',
            'icon' => 'fa-users',
            'title' => __('landing.feature_members_title'),
            'desc' => __('landing.feature_members_desc'),
            'preview' => __('landing.feature_members_preview'),
            'stats' => [
                ['label' => __('landing.mock_members_label'), 'value' => '1,248'],
                ['label' => __('landing.stat_branches'), 'value' => '4'],
                ['label' => __('landing.stat_reports'), 'value' => '12'],
            ],
        ],
        [
            'id' => 'finance',
            'icon' => 'fa-money',
            'title' => __('landing.feature_finance_title'),
            'desc' => __('landing.feature_finance_desc'),
            'preview' => __('landing.feature_finance_preview'),
            'stats' => [
                ['label' => __('landing.mock_giving_label'), 'value' => 'TZS 8.4M'],
                ['label' => __('landing.stat_tithes'), 'value' => '62%'],
                ['label' => __('landing.stat_pledges'), 'value' => '18'],
            ],
        ],
        [
            'id' => 'services',
            'icon' => 'fa-calendar-check-o',
            'title' => __('landing.feature_services_title'),
            'desc' => __('landing.feature_services_desc'),
            'preview' => __('landing.feature_services_preview'),
            'stats' => [
                ['label' => __('landing.mock_this_sunday'), 'value' => '86%'],
                ['label' => __('landing.mock_present'), 'value' => '412'],
                ['label' => __('landing.stat_events'), 'value' => '3'],
            ],
        ],
        [
            'id' => 'announcements',
            'icon' => 'fa-bullhorn',
            'title' => __('landing.feature_announcements_title'),
            'desc' => __('landing.feature_announcements_desc'),
            'preview' => __('landing.feature_announcements_preview'),
            'stats' => [
                ['label' => __('landing.stat_sms'), 'value' => '2.4k'],
                ['label' => __('landing.stat_sent'), 'value' => '94%'],
                ['label' => __('landing.stat_drafts'), 'value' => '5'],
            ],
        ],
        [
            'id' => 'branches',
            'icon' => 'fa-sitemap',
            'title' => __('landing.feature_branches_title'),
            'desc' => __('landing.feature_branches_desc'),
            'preview' => __('landing.feature_branches_preview'),
            'stats' => [
                ['label' => __('landing.stat_branches'), 'value' => '6'],
                ['label' => __('landing.stat_roles'), 'value' => '14'],
                ['label' => __('landing.stat_staff'), 'value' => '28'],
            ],
        ],
        [
            'id' => 'reports',
            'icon' => 'fa-bar-chart',
            'title' => __('landing.feature_reports_title'),
            'desc' => __('landing.feature_reports_desc'),
            'preview' => __('landing.feature_reports_preview'),
            'stats' => [
                ['label' => __('landing.stat_reports'), 'value' => '24'],
                ['label' => __('landing.stat_exports'), 'value' => '9'],
                ['label' => __('landing.stat_trends'), 'value' => '+12%'],
            ],
        ],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $appName }} — {{ __('landing.tagline') }}</title>
    <meta name="description" content="{{ $appName }} {{ __('landing.meta_description') }}">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    {{-- Inline from resources/css so live matches local even when public/css is stale on the server. --}}
    @include('partials.inline-resource-css', ['file' => 'landing.css'])
    <link rel="icon" href="{{ $logoUrl ?? \App\Support\WauminiBrand::publicAsset('waumini_link_logo.png') }}" type="image/png">
    <style>
        :root { --brand: {{ $brand }}; --brand-dark: #6e0000; --nav-height: 4.25rem; }
    </style>
</head>
<body
    class="landing-page"
    x-data="{
        open: false,
        scrolled: false,
        activeSection: 'home'
    }"
    @scroll.window="scrolled = (window.pageYOffset || document.documentElement.scrollTop) > 24"
    :class="{ 'is-scrolled': scrolled }"
    @landing-section.window="activeSection = $event.detail.id"
>
<div class="landing-shell">

    <header class="landing-nav" :class="{ 'is-scrolled': scrolled, 'is-open': open }">
        <div class="landing-container landing-nav-wrap">
            <div class="landing-nav-inner">
                <a href="#home" class="landing-brand" @click="open = false">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="landing-brand-logo">
                    @else
                        <span class="landing-brand-mark"><i class="fa fa-link"></i></span>
                        <span class="landing-brand-text">{{ $appName }}</span>
                    @endif
                </a>

                <button type="button" class="landing-menu-toggle landing-hide-desktop" @click="open = !open" :aria-expanded="open.toString()" aria-label="{{ __('landing.toggle_menu') }}">
                    <i class="fa" :class="open ? 'fa-times' : 'fa-bars'"></i>
                </button>

                <nav class="landing-menu landing-nav-center" :class="{ 'is-open': open }" aria-label="Primary">
                    <a href="#home" @click="open = false" :class="{ 'is-active': activeSection === 'home' }">{{ __('landing.home') }}</a>
                    <a href="#features" @click="open = false" :class="{ 'is-active': activeSection === 'features' }">{{ __('landing.features') }}</a>
                    <a href="#about" @click="open = false" :class="{ 'is-active': activeSection === 'about' }">{{ __('landing.about') }}</a>
                    <a href="#pricing" @click="open = false" :class="{ 'is-active': activeSection === 'pricing' }">{{ __('landing.pricing') }}</a>
                    <a href="#demo" @click="open = false" :class="{ 'is-active': activeSection === 'demo' }">{{ __('landing.request_demo') }}</a>
                    <a href="#contact" @click="open = false" :class="{ 'is-active': activeSection === 'contact' }">{{ __('landing.contact') }}</a>
                </nav>

                <div class="landing-nav-actions">
                    @include('partials.locale-switcher', ['variant' => 'links', 'class' => 'landing-locale-switcher'])
                    <a href="{{ route('church.login') }}" class="landing-btn landing-btn-ghost landing-hide-mobile">{{ __('landing.church_login') }}</a>
                    <a href="#demo" class="landing-btn landing-btn-primary">{{ __('landing.request_demo') }}</a>
                </div>
            </div>
        </div>
    </header>

    <section id="home" class="landing-hero" data-section="home">
        <div class="landing-hero-atmosphere" aria-hidden="true">
            <div class="landing-hero-orb landing-hero-orb--one"></div>
            <div class="landing-hero-orb landing-hero-orb--two"></div>
            <div class="landing-hero-grid-lines"></div>
        </div>

        <div class="landing-container landing-hero-grid">
            <div class="landing-hero-copy reveal is-visible" data-reveal>
                <p class="landing-brand-signal">{{ $appName }}</p>
                <h1>{!! __('landing.hero_title') !!}</h1>
                <p class="landing-hero-lead">
                    {{ __('landing.hero_lead', ['app' => $appName]) }}
                </p>
                <div class="landing-hero-actions">
                    <a href="#demo" class="landing-btn landing-btn-primary landing-btn-lg">
                        {{ __('landing.request_demo') }}
                    </a>
                    <a href="#pricing" class="landing-btn landing-btn-ghost landing-btn-lg">
                        {{ __('landing.view_pricing') }}
                    </a>
                </div>
            </div>

            <div class="landing-hero-visual reveal is-visible" data-reveal data-reveal-delay="120" aria-hidden="false">
                <div class="landing-mock" role="img" aria-label="{{ $appName }} {{ __('landing.mock_dashboard') }}">
                    <div class="landing-mock-chrome">
                        <span></span><span></span><span></span>
                        <strong>{{ $appName }}</strong>
                    </div>
                    <div class="landing-mock-body">
                        <aside class="landing-mock-side">
                            <div class="landing-mock-nav is-active"></div>
                            <div class="landing-mock-nav"></div>
                            <div class="landing-mock-nav"></div>
                            <div class="landing-mock-nav"></div>
                        </aside>
                        <div class="landing-mock-main">
                            <div class="landing-mock-headline">
                                <span>{{ __('landing.mock_dashboard') }}</span>
                                <em>{{ __('landing.mock_this_sunday') }}</em>
                            </div>
                            <div class="landing-mock-kpis">
                                <div class="landing-mock-kpi">
                                    <small>{{ __('landing.mock_members_label') }}</small>
                                    <strong>1,248</strong>
                                    <div class="landing-mock-bar"><i style="width:78%"></i></div>
                                </div>
                                <div class="landing-mock-kpi">
                                    <small>{{ __('landing.mock_giving_label') }}</small>
                                    <strong>8.4M</strong>
                                    <div class="landing-mock-bar"><i style="width:64%"></i></div>
                                </div>
                                <div class="landing-mock-kpi accent">
                                    <small>{{ __('landing.mock_attendance_label') }}</small>
                                    <strong>86%</strong>
                                    <div class="landing-mock-bar"><i style="width:86%"></i></div>
                                </div>
                            </div>
                            <div class="landing-mock-chart">
                                <div class="landing-mock-chart-bars">
                                    <span style="--h:42%"></span>
                                    <span style="--h:58%"></span>
                                    <span style="--h:46%"></span>
                                    <span style="--h:72%"></span>
                                    <span style="--h:64%"></span>
                                    <span style="--h:88%"></span>
                                    <span style="--h:76%"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-section" id="features" data-section="features">
        <div class="landing-container">
            <div class="landing-section-head reveal" data-reveal>
                <span class="eyebrow">{{ __('landing.features_eyebrow') }}</span>
                <h2>{{ __('landing.features_title') }}</h2>
                <p>{{ __('landing.features_lead') }}</p>
            </div>

            <div class="landing-feature-grid">
                @foreach($features as $feature)
                    <article class="landing-feature-card reveal" data-reveal>
                        <div class="landing-feature-card-icon" aria-hidden="true">
                            <i class="fa {{ $feature['icon'] }}"></i>
                        </div>
                        <h3>{{ $feature['title'] }}</h3>
                        <p>{{ $feature['desc'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="landing-section alt" id="about" data-section="about">
        <div class="landing-container landing-about">
            <div class="landing-about-copy reveal" data-reveal>
                <span class="eyebrow">{{ __('landing.about_eyebrow', ['app' => $appName]) }}</span>
                <h2>{{ __('landing.about_title') }}</h2>
                <p class="landing-about-lead">
                    {{ __('landing.about_lead', ['app' => $appName]) }}
                </p>
                <ul class="landing-checklist">
                    <li><i class="fa fa-check"></i><span>{{ __('landing.about_check_1') }}</span></li>
                    <li><i class="fa fa-check"></i><span>{{ __('landing.about_check_2') }}</span></li>
                    <li><i class="fa fa-check"></i><span>{{ __('landing.about_check_3') }}</span></li>
                    <li><i class="fa fa-check"></i><span>{{ __('landing.about_check_4') }}</span></li>
                </ul>

                <div class="landing-about-audience">
                    <a href="{{ route('church.login') }}" class="landing-audience-link">
                        <strong>{{ __('landing.audience_leaders') }}</strong>
                        <span>{{ __('landing.audience_leaders_desc') }}</span>
                        <em>{{ __('landing.open_dashboard') }} <i class="fa fa-arrow-right"></i></em>
                    </a>
                    <a href="{{ route('church.login') }}" class="landing-audience-link">
                        <strong>{{ __('landing.audience_members') }}</strong>
                        <span>{{ __('landing.audience_members_desc') }}</span>
                        <em>{{ __('landing.member_login') }} <i class="fa fa-arrow-right"></i></em>
                    </a>
                </div>
            </div>

            <div class="landing-about-visual reveal" data-reveal data-reveal-delay="100">
                <div class="landing-about-panel">
                    <div class="landing-about-panel-item">
                        <i class="fa fa-shield"></i>
                        <div>
                            <strong>{{ __('landing.panel_secure_title') }}</strong>
                            <span>{{ __('landing.panel_secure_desc') }}</span>
                        </div>
                    </div>
                    <div class="landing-about-panel-item">
                        <i class="fa fa-cloud"></i>
                        <div>
                            <strong>{{ __('landing.panel_cloud_title') }}</strong>
                            <span>{{ __('landing.panel_cloud_desc') }}</span>
                        </div>
                    </div>
                    <div class="landing-about-panel-chart" aria-hidden="true">
                        <span style="--h:48%"></span>
                        <span style="--h:66%"></span>
                        <span style="--h:52%"></span>
                        <span style="--h:80%"></span>
                        <span style="--h:70%"></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-section" id="how" data-section="how">
        <div class="landing-container">
            <div class="landing-section-head reveal" data-reveal>
                <span class="eyebrow">{{ __('landing.how_eyebrow') }}</span>
                <h2>{{ __('landing.how_title') }}</h2>
                <p>{{ __('landing.how_lead') }}</p>
            </div>
            <div class="landing-steps">
                <div class="landing-step reveal" data-reveal data-reveal-delay="0">
                    <div class="landing-step-num">1</div>
                    <h3>{{ __('landing.step_1_title') }}</h3>
                    <p>{{ __('landing.step_1_desc') }}</p>
                </div>
                <div class="landing-step reveal" data-reveal data-reveal-delay="80">
                    <div class="landing-step-num">2</div>
                    <h3>{{ __('landing.step_2_title') }}</h3>
                    <p>{{ __('landing.step_2_desc') }}</p>
                </div>
                <div class="landing-step reveal" data-reveal data-reveal-delay="160">
                    <div class="landing-step-num">3</div>
                    <h3>{{ __('landing.step_3_title') }}</h3>
                    <p>{{ __('landing.step_3_desc') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-section alt" id="pricing" data-section="pricing">
        <div class="landing-container">
            <div class="landing-section-head reveal" data-reveal>
                <span class="eyebrow">{{ __('landing.pricing_eyebrow') }}</span>
                <h2>{{ __('landing.pricing_title') }}</h2>
                <p>{{ str_replace(':trial', $packages->isNotEmpty() ? __('landing.pricing_lead_trial') : '', __('landing.pricing_lead')) }}</p>
            </div>

            <div class="landing-pricing-grid">
                @forelse($packages as $package)
                    @php
                        $isPopular = $packages->count() > 1 && $loop->index === (int) floor($packages->count() / 2);
                    @endphp
                    <article class="landing-price-card reveal {{ $isPopular ? 'popular' : '' }}" data-reveal data-reveal-delay="{{ $loop->index * 70 }}">
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
                        <a href="#demo" class="landing-btn {{ $isPopular ? 'landing-btn-primary' : 'landing-btn-ghost' }}">
                            {{ __('landing.request_demo') }}
                        </a>
                    </article>
                @empty
                    <div class="landing-pricing-empty reveal" data-reveal>
                        <p>{{ __('landing.pricing_empty') }}</p>
                        <a href="#demo" class="landing-btn landing-btn-primary">{{ __('landing.contact_get_started') }}</a>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="landing-section" id="demo" data-section="demo">
        <div class="landing-container">
            <div class="landing-section-head reveal" data-reveal>
                <span class="eyebrow">{{ __('landing.demo_eyebrow') }}</span>
                <h2>{{ __('landing.demo_title') }}</h2>
                <p>{{ __('landing.demo_lead') }}</p>
            </div>

            @if(session('demo_success'))
                <div class="landing-demo-alert landing-demo-alert--success reveal is-visible" role="status">
                    {{ session('demo_success') }}
                </div>
            @endif

                    @if($errors->any() && old('_form') !== 'contact')
                <div class="landing-demo-alert landing-demo-alert--error reveal is-visible" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('demo-request.store') }}" class="landing-demo-form reveal" data-reveal>
                @csrf
                <input type="hidden" name="_form" value="demo">
                <div class="landing-demo-grid">
                    <label class="landing-field">
                        <span>{{ __('landing.demo_church_name') }}</span>
                        <input type="text" name="church_name" value="{{ old('_form') === 'demo' ? old('church_name') : '' }}" required maxlength="255" placeholder="{{ __('landing.demo_church_name_ph') }}">
                    </label>
                    <label class="landing-field">
                        <span>{{ __('landing.demo_contact_name') }}</span>
                        <input type="text" name="contact_name" value="{{ old('_form') === 'demo' ? old('contact_name') : '' }}" required maxlength="150" placeholder="{{ __('landing.demo_contact_name_ph') }}">
                    </label>
                    <label class="landing-field">
                        <span>{{ __('landing.demo_email') }}</span>
                        <input type="email" name="email" value="{{ old('_form') === 'demo' ? old('email') : '' }}" required maxlength="255" placeholder="{{ __('landing.demo_email_ph') }}">
                    </label>
                    <label class="landing-field">
                        <span>{{ __('landing.demo_phone') }}</span>
                        <input type="text" name="phone" value="{{ old('_form') === 'demo' ? old('phone') : '' }}" required maxlength="50" placeholder="{{ __('landing.demo_phone_ph') }}">
                    </label>
                    <label class="landing-field">
                        <span>{{ __('landing.demo_city') }}</span>
                        <input type="text" name="city" value="{{ old('_form') === 'demo' ? old('city') : '' }}" maxlength="100" placeholder="{{ __('landing.demo_city_ph') }}">
                    </label>
                    <label class="landing-field">
                        <span>{{ __('landing.demo_country') }}</span>
                        <input type="text" name="country" value="{{ old('_form') === 'demo' ? old('country', 'Tanzania') : 'Tanzania' }}" maxlength="100" placeholder="{{ __('landing.demo_country_ph') }}">
                    </label>
                    <label class="landing-field landing-field--full">
                        <span>{{ __('landing.demo_package') }}</span>
                        <select name="package_id">
                            <option value="">{{ __('landing.demo_package_any') }}</option>
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}" @selected(old('_form') === 'demo' && (string) old('package_id') === (string) $package->id)>
                                    {{ $package->name }} — {{ $package->trial_days }} {{ __('landing.demo_trial_days') }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label class="landing-field landing-field--full">
                        <span>{{ __('landing.demo_message') }}</span>
                        <textarea name="message" rows="4" maxlength="2000" placeholder="{{ __('landing.demo_message_ph') }}">{{ old('_form') === 'demo' ? old('message') : '' }}</textarea>
                    </label>
                </div>
                <div class="landing-demo-actions">
                    <button type="submit" class="landing-btn landing-btn-primary landing-btn-lg">
                        {{ __('landing.demo_submit') }}
                    </button>
                    <p class="landing-demo-note">{{ __('landing.demo_note') }}</p>
                </div>
            </form>
        </div>
    </section>

    <section class="landing-section alt" id="contact" data-section="contact">
        <div class="landing-container">
            <div class="landing-section-head reveal" data-reveal>
                <span class="eyebrow">{{ __('landing.contact_eyebrow') }}</span>
                <h2>{{ __('landing.contact_title') }}</h2>
                <p>{{ __('landing.contact_lead') }}</p>
            </div>

            <div class="landing-contact-layout reveal" data-reveal>
                <div class="landing-contact-info">
                    <article class="landing-contact-card">
                        <div class="landing-contact-card-icon"><i class="fa fa-map-marker"></i></div>
                        <div>
                            <strong>{{ __('landing.contact_visit') }}</strong>
                            <p>
                                Ben Bella Street, Moshi Municipality<br>
                                Opposite High Court of Tanzania<br>
                                P.O. Box 20, Moshi – Kilimanjaro<br>
                                Postcode: 25101
                            </p>
                        </div>
                    </article>
                    <article class="landing-contact-card">
                        <div class="landing-contact-card-icon"><i class="fa fa-phone"></i></div>
                        <div>
                            <strong>{{ __('landing.call_us') }}</strong>
                            <p><a href="tel:+255749719998">+255 749 719 998</a></p>
                        </div>
                    </article>
                    <article class="landing-contact-card">
                        <div class="landing-contact-card-icon"><i class="fa fa-envelope-o"></i></div>
                        <div>
                            <strong>{{ __('landing.mail_us') }}</strong>
                            <p><a href="mailto:emca@emca.tech">emca@emca.tech</a></p>
                        </div>
                    </article>
                </div>

                <div class="landing-contact-form-wrap">
                    @if(session('contact_success'))
                        <div class="landing-demo-alert landing-demo-alert--success" role="status">
                            {{ session('contact_success') }}
                        </div>
                    @endif

                    @if($errors->any() && old('_form') === 'contact')
                        <div class="landing-demo-alert landing-demo-alert--error" role="alert">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('contact.store') }}" class="landing-demo-form landing-contact-form">
                        @csrf
                        <input type="hidden" name="_form" value="contact">
                        <div class="landing-demo-grid">
                            <label class="landing-field">
                                <span>{{ __('landing.contact_name') }}</span>
                                <input type="text" name="name" value="{{ old('_form') === 'contact' ? old('name') : '' }}" required maxlength="150" placeholder="{{ __('landing.contact_name_ph') }}">
                            </label>
                            <label class="landing-field">
                                <span>{{ __('landing.contact_email') }}</span>
                                <input type="email" name="email" value="{{ old('_form') === 'contact' ? old('email') : '' }}" required maxlength="255" placeholder="{{ __('landing.contact_email_ph') }}">
                            </label>
                            <label class="landing-field">
                                <span>{{ __('landing.contact_phone') }}</span>
                                <input type="text" name="phone" value="{{ old('_form') === 'contact' ? old('phone') : '' }}" maxlength="50" placeholder="{{ __('landing.demo_phone_ph') }}">
                            </label>
                            <label class="landing-field">
                                <span>{{ __('landing.contact_subject') }}</span>
                                <input type="text" name="subject" value="{{ old('_form') === 'contact' ? old('subject') : '' }}" required maxlength="200" placeholder="{{ __('landing.contact_subject_ph') }}">
                            </label>
                            <label class="landing-field landing-field--full">
                                <span>{{ __('landing.contact_message') }}</span>
                                <textarea name="message" rows="4" maxlength="3000" required placeholder="{{ __('landing.contact_message_ph') }}">{{ old('_form') === 'contact' ? old('message') : '' }}</textarea>
                            </label>
                        </div>
                        <div class="landing-demo-actions">
                            <button type="submit" class="landing-btn landing-btn-primary landing-btn-lg">
                                {{ __('landing.contact_submit') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-section" id="cta">
        <div class="landing-container">
            <div class="landing-cta reveal" data-reveal>
                <h2>{{ __('landing.cta_title') }}</h2>
                <p>{{ __('landing.cta_lead', ['app' => $appName]) }}</p>
                <div class="landing-cta-actions">
                    <a href="#demo" class="landing-btn landing-btn-primary landing-cta-btn-light">
                        {{ __('landing.request_demo') }}
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
                        <li><a href="#demo">{{ __('landing.request_demo') }}</a></li>
                        <li><a href="#contact">{{ __('landing.contact') }}</a></li>
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

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script src="{{ $landingJs }}?v={{ $landingJsVersion }}" defer></script>
</body>
</html>
