@php
    $s = fn (string $path) => \App\Support\WauminiBrand::publicAsset('skilline/'.$path);
    $brand = config('waumini.brand_color', '#940000');
    $logoUrl = \App\Support\WauminiBrand::logoUrl();
    $appName = \App\Support\WauminiBrand::appDisplayName();
    $landingCss = \App\Support\WauminiBrand::publicAsset('css/landing.css');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $appName }} — Church Management Platform</title>
    <meta name="description" content="{{ $appName }} helps churches manage members, finance, attendance, communications, and more — all in one secure cloud platform.">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="{{ $landingCss }}?v=11">
    <link rel="icon" href="{{ $logoUrl ?? \App\Support\WauminiBrand::publicAsset('waumini_link_logo.png') }}" type="image/png">
    <style>
        :root { --brand: {{ $brand }}; --brand-dark: {{ $brand }}; }
        html, body.landing-page { overflow-x: clip; max-width: 100%; width: 100%; }
    </style>
</head>
<body class="landing-page" x-data="{ open: false }">

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

                <button type="button" class="landing-menu-toggle landing-hide-desktop" @click="open = !open" aria-label="Toggle menu">
                    <i class="fa" :class="open ? 'fa-times' : 'fa-bars'"></i>
                </button>

                <nav class="landing-menu landing-nav-center" :class="{ 'is-open': open }">
                    <a href="#home" @click="open = false">Home</a>
                    <a href="#features" @click="open = false">Features</a>
                    <a href="#about" @click="open = false">About</a>
                    <a href="#pricing" @click="open = false">Pricing</a>
                    <a href="{{ route('church.login') }}" class="landing-btn landing-btn-primary landing-menu-cta" @click="open = false">Get Started</a>
                </nav>

                <div class="landing-nav-actions">
                    <a href="{{ route('church.login') }}" class="landing-btn landing-btn-ghost landing-hide-mobile">Church Login</a>
                    <a href="{{ route('church.login') }}" class="landing-btn landing-btn-primary">Get Started</a>
                </div>
            </div>
        </div>
    </header>

    <section id="home" class="landing-hero">
        <div class="landing-hero-deco one" aria-hidden="true"></div>
        <div class="landing-hero-deco two" aria-hidden="true"></div>

        <div class="landing-container landing-hero-grid">
            <div data-aos="fade-up">
                <div class="landing-eyebrow">
                    <i class="fa fa-shield"></i> Trusted church management platform
                </div>
                <h1>Run your church with <span>clarity</span> and confidence</h1>
                <p class="landing-hero-lead">
                    {{ $appName }} brings members, finance, attendance, SMS, and leadership tools into one beautiful dashboard — built for African churches.
                </p>
                <div class="landing-hero-actions">
                    <a href="{{ route('church.login') }}" class="landing-btn landing-btn-primary">
                        <i class="fa fa-sign-in"></i> Sign in to your church
                    </a>
                    <a href="#pricing" class="landing-btn landing-btn-ghost">
                        <i class="fa fa-tags"></i> View pricing
                    </a>
                </div>
                <div class="landing-stats">
                    <div class="landing-stat">
                        <strong>All-in-one</strong>
                        <span>Members & finance</span>
                    </div>
                    <div class="landing-stat">
                        <strong>Secure</strong>
                        <span>Role-based access</span>
                    </div>
                    <div class="landing-stat">
                        <strong>Cloud</strong>
                        <span>Access anywhere</span>
                    </div>
                </div>
            </div>

            <div class="landing-hero-visual" data-aos="fade-up">
                <div class="landing-hero-badge calendar">
                    <img src="{{ $s('img/calendar.svg') }}" alt="">
                </div>
                <img src="{{ $s('img/girl-laptop.png') }}" alt="{{ $appName }} dashboard preview">
                <div class="landing-float-card one">
                    <strong><i class="fa fa-calendar-check-o landing-icon-accent"></i> Sunday attendance</strong>
                    <span>Tracked in real time</span>
                </div>
                <div class="landing-float-card two">
                    <strong><i class="fa fa-money landing-icon-accent"></i> Tithes & offerings</strong>
                    <span>Clear finance reports</span>
                </div>
            </div>
        </div>
        <svg class="landing-wave" viewBox="0 0 1200 120" preserveAspectRatio="none" aria-hidden="true">
            <path d="M600,112.77C268.63,112.77,0,65.52,0,7.23V120H1200V7.23C1200,65.52,931.37,112.77,600,112.77Z" fill="currentColor"></path>
        </svg>
    </section>

    <section class="landing-section" id="features">
        <div class="landing-container">
            <div class="landing-pill-row" data-aos="fade-up">
                <span class="landing-pill"><i class="fa fa-users"></i> Members</span>
                <span class="landing-pill"><i class="fa fa-money"></i> Finance</span>
                <span class="landing-pill"><i class="fa fa-calendar"></i> Attendance</span>
                <span class="landing-pill"><i class="fa fa-envelope"></i> SMS</span>
                <span class="landing-pill"><i class="fa fa-line-chart"></i> Reports</span>
            </div>

            <div class="landing-section-head" data-aos="fade-up">
                <span class="eyebrow">Features</span>
                <h2>Everything your church team needs in one place</h2>
                <p>From the secretary’s desk to the treasurer’s office — manage people, money, and ministry without scattered spreadsheets.</p>
            </div>

            <div class="landing-feature-grid">
                <article class="landing-feature-card" data-aos="fade-up">
                    <div class="landing-feature-icon" style="background: linear-gradient(135deg, #5b72ee, #7b8cff);">
                        <i class="fa fa-users"></i>
                    </div>
                    <h3>Member Management</h3>
                    <p>Register members, families, leadership, and keep your membership records organized.</p>
                </article>
                <article class="landing-feature-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="landing-feature-icon" style="background: linear-gradient(135deg, #940000, #c41e1e);">
                        <i class="fa fa-money"></i>
                    </div>
                    <h3>Finance & Giving</h3>
                    <p>Record tithes, offerings, pledges, budgets, and expenses with approval workflows.</p>
                </article>
                <article class="landing-feature-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="landing-feature-icon" style="background: linear-gradient(135deg, #29b9e7, #5ed0f7);">
                        <i class="fa fa-calendar-check-o"></i>
                    </div>
                    <h3>Services & Attendance</h3>
                    <p>Plan services, track attendance, and manage special events with ease.</p>
                </article>
                <article class="landing-feature-card" data-aos="fade-up" data-aos-delay="50">
                    <div class="landing-feature-icon" style="background: linear-gradient(135deg, #f48c06, #ffb347);">
                        <i class="fa fa-bullhorn"></i>
                    </div>
                    <h3>Announcements & SMS</h3>
                    <p>Reach your congregation with announcements and SMS notifications.</p>
                </article>
                <article class="landing-feature-card" data-aos="fade-up" data-aos-delay="150">
                    <div class="landing-feature-icon" style="background: linear-gradient(135deg, #33c18f, #5fe0ad);">
                        <i class="fa fa-sitemap"></i>
                    </div>
                    <h3>Branches & Roles</h3>
                    <p>Support multiple branches with role-based permissions for each staff member.</p>
                </article>
                <article class="landing-feature-card" data-aos="fade-up" data-aos-delay="250">
                    <div class="landing-feature-icon" style="background: linear-gradient(135deg, #2f327d, #4b4f9b);">
                        <i class="fa fa-bar-chart"></i>
                    </div>
                    <h3>Reports & Analytics</h3>
                    <p>Member summaries, giving reports, and budget performance at a glance.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="landing-section alt" id="about">
        <div class="landing-container landing-about">
            <div class="landing-about-copy" data-aos="fade-up">
                <span class="eyebrow">About {{ $appName }}</span>
                <h2>Built for how churches really work</h2>
                <p class="landing-about-lead">
                    {{ $appName }} is a cloud platform that helps your leadership team stay organized — members, finances, attendance, SMS, and reports in one secure place.
                </p>
                <ul class="landing-checklist">
                    <li><i class="fa fa-check-circle"></i><span>One dashboard for pastors, secretaries, treasurers, and admins</span></li>
                    <li><i class="fa fa-check-circle"></i><span>Member portal so your congregation can stay connected</span></li>
                    <li><i class="fa fa-check-circle"></i><span>Multi-branch support with role-based permissions</span></li>
                    <li><i class="fa fa-check-circle"></i><span>Assets, departments, bereavements, and more</span></li>
                </ul>

                <div class="landing-about-audience">
                    <div class="landing-audience-card">
                        <div class="landing-audience-icon"><i class="fa fa-users"></i></div>
                        <div>
                            <h4>Church leaders</h4>
                            <p>Manage members, finance, services, and ministry operations.</p>
                            <a href="{{ route('church.login') }}">Open dashboard <i class="fa fa-arrow-right"></i></a>
                        </div>
                    </div>
                    <div class="landing-audience-card">
                        <div class="landing-audience-icon alt"><i class="fa fa-user"></i></div>
                        <div>
                            <h4>Members</h4>
                            <p>View announcements, giving history, and church updates.</p>
                            <a href="{{ route('church.login') }}">Member login <i class="fa fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="landing-about-visual" data-aos="fade-up">
                <div class="landing-about-frame">
                    <img src="{{ $s('img/teacher-explaining.png') }}" alt="Church team using {{ $appName }}">
                </div>
                <div class="landing-about-badge">
                    <i class="fa fa-cloud"></i>
                    <div>
                        <strong>Cloud-based</strong>
                        <span>Secure & accessible anywhere</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-section">
        <div class="landing-container">
            <div class="landing-section-head" data-aos="fade-up">
                <span class="eyebrow">How it works</span>
                <h2>Get started in three simple steps</h2>
                <p>Your church gets onboarded, your team logs in, and you start managing with confidence.</p>
            </div>
            <div class="landing-steps">
                <div class="landing-step" data-aos="fade-up">
                    <div class="landing-step-num">1</div>
                    <div>
                        <h3>Choose your plan</h3>
                        <p>Pick a package that fits your church size and ministry needs.</p>
                    </div>
                </div>
                <div class="landing-step" data-aos="fade-up" data-aos-delay="100">
                    <div class="landing-step-num">2</div>
                    <div>
                        <h3>Set up your church</h3>
                        <p>Add members, configure branches, and invite your leadership team.</p>
                    </div>
                </div>
                <div class="landing-step" data-aos="fade-up" data-aos-delay="200">
                    <div class="landing-step-num">3</div>
                    <div>
                        <h3>Manage with confidence</h3>
                        <p>Track giving, attendance, and communication from one place.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-section alt" id="pricing">
        <div class="landing-container">
            <div class="landing-section-head" data-aos="fade-up">
                <span class="eyebrow">Pricing</span>
                <h2>Simple, transparent plans</h2>
                <p>Every plan includes secure cloud hosting{{ $packages->isNotEmpty() ? ' and a free trial period' : '' }}. Prices are managed by your platform administrator.</p>
            </div>

            <div class="landing-pricing-grid">
                @forelse($packages as $package)
                    @php
                        $isPopular = $packages->count() > 1 && $loop->index === (int) floor($packages->count() / 2);
                    @endphp
                    <article class="landing-price-card {{ $isPopular ? 'popular' : '' }}" data-aos="fade-up" data-aos-delay="{{ $loop->index * 80 }}">
                        @if($isPopular)
                            <span class="landing-price-badge">Most popular</span>
                        @endif
                        <h3>{{ $package->name }}</h3>
                        <p class="desc">{{ $package->description }}</p>
                        @include('partials.public-package-pricing', ['package' => $package, 'currencyCode' => $currencyCode])
                        <ul class="landing-price-features">
                            @if($package->max_members)
                                <li><i class="fa fa-check"></i><span>Up to {{ number_format($package->max_members) }} members</span></li>
                            @else
                                <li><i class="fa fa-check"></i><span>Unlimited members</span></li>
                            @endif
                            @if($package->max_sms_monthly)
                                <li><i class="fa fa-check"></i><span>{{ number_format($package->max_sms_monthly) }} SMS / month</span></li>
                            @endif
                            @foreach($package->features->filter(fn ($feature) => (bool) $feature->pivot?->is_enabled) as $feature)
                                <li><i class="fa fa-check"></i><span>{{ $feature->name }}</span></li>
                            @endforeach
                        </ul>
                        <a href="{{ route('church.login') }}" class="landing-btn {{ $isPopular ? 'landing-btn-primary' : 'landing-btn-ghost' }}" style="width: 100%;">
                            Get started
                        </a>
                    </article>
                @empty
                    <div style="grid-column: 1 / -1; text-align: center; color: var(--ink-soft); padding: 3rem 0;">
                        <p>Pricing plans will be published soon.</p>
                        <a href="{{ route('church.login') }}" class="landing-btn landing-btn-primary" style="margin-top: 1rem;">Contact us to get started</a>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="landing-section">
        <div class="landing-container">
            <div class="landing-cta" data-aos="zoom-in">
                <h2>Ready to modernize your church administration?</h2>
                <p>Join churches using {{ $appName }} to manage members, finances, and ministry with less paperwork and more impact.</p>
                <div style="display: flex; flex-wrap: wrap; gap: 0.85rem; justify-content: center;">
                    <a href="{{ route('church.login') }}" class="landing-btn landing-btn-primary" style="background: #fff; color: var(--brand); box-shadow: none;">
                        <i class="fa fa-rocket"></i> Get started today
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
                    <p>Church management platform for members, finance, attendance, and communications — built for modern ministry teams.</p>
                    <div class="landing-footer-highlights">
                        <span><i class="fa fa-shield"></i> Secure cloud</span>
                        <span><i class="fa fa-users"></i> Multi-branch</span>
                        <span><i class="fa fa-mobile"></i> Access anywhere</span>
                    </div>
                </div>

                <div class="landing-footer-col">
                    <h4>Explore</h4>
                    <ul>
                        <li><a href="#home">Home</a></li>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#about">About</a></li>
                        <li><a href="#pricing">Pricing</a></li>
                    </ul>
                </div>

                <div class="landing-footer-col">
                    <h4>Access</h4>
                    <ul>
                        <li><a href="{{ route('church.login') }}">Church Login</a></li>
                        <li><a href="{{ route('owner.login') }}">Platform Admin</a></li>
                        <li><a href="{{ route('church.login') }}">Get Started</a></li>
                    </ul>
                </div>

                <div class="landing-footer-col landing-footer-contact-col">
                    <h4>Contact</h4>
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
                                <span class="landing-contact-label">Call Us Now</span>
                                <a href="tel:+255749719998">+255 749 719 998</a>
                            </span>
                        </li>
                        <li>
                            <i class="fa fa-envelope-o"></i>
                            <span>
                                <span class="landing-contact-label">Mail Us Now</span>
                                <a href="mailto:emca@emca.tech">emca@emca.tech</a>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="landing-footer-bottom">
            <div class="landing-container landing-footer-bottom-inner">
                <p>&copy; {{ date('Y') }} {{ $appName }}. All rights reserved.</p>
                <p class="landing-footer-powered">Powered by <a href="https://www.emca.tech" target="_blank" rel="noopener noreferrer">EmCa Technologies</a></p>
            </div>
        </div>
    </footer>

    <script type="module" src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 700,
            once: true,
            offset: 80,
            disable: function () {
                return window.innerWidth < 992;
            },
        });
    </script>
</body>
</html>
