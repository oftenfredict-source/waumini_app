@php $vali = asset('vali-master/docs'); @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Church Login - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ $vali }}/css/main.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    @include('partials.brand-styles')
    <style>
        .login-content .logo-mark {
            display: inline-block;
            background: #fff;
            border-radius: 0.65rem;
            padding: 0.5rem 0.85rem;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            margin-bottom: 1.5rem;
        }
        .login-content .logo-mark img {
            display: block;
            max-height: 4.5rem;
            width: auto;
            max-width: min(280px, 80vw);
            object-fit: contain;
        }
        .login-content .logo-mark h1 {
            margin: 0;
            font-size: 2rem;
            color: var(--waumini-brand, #940000);
        }
    </style>
</head>
<body>
    <section class="material-half-bg"><div class="cover"></div></section>
    <section class="login-content">
        <div class="logo text-center">
            @if($logoUrl = \App\Support\WauminiBrand::logoUrl())
                <div class="logo-mark">
                    <img src="{{ $logoUrl }}" alt="{{ \App\Support\WauminiBrand::appDisplayName() }}">
                </div>
            @else
                <h1>{{ \App\Support\WauminiBrand::appDisplayName() }}</h1>
            @endif
        </div>
        <div class="login-box">
            <form class="login-form" method="POST" action="{{ route('church.login.submit') }}">
                @csrf
                <h3 class="login-head"><i class="fa fa-lg fa-fw fa-building"></i> CHURCH SIGN IN</h3>

                @if(!empty($ownerSessionActive))
                    <div class="alert alert-info">
                        You are signed in to the owner dashboard. Signing in here will switch to your church account.
                    </div>
                @endif

                @include('partials.sweetalert-flash')

                <div class="form-group">
                    <label class="control-label">EMAIL / MEMBER ID</label>
                    <input class="form-control @error('email') is-invalid @enderror" type="text" name="email"
                        value="{{ old('email') }}" placeholder="admin@church.org or IM-2026-0001" autofocus required>
                    @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                    <small class="text-muted">Church admins: use your login email from church setup. Members: use your Member ID.</small>
                </div>
                <div class="form-group">
                    <label class="control-label">PASSWORD</label>
                    <input class="form-control @error('password') is-invalid @enderror" type="password" name="password" required>
                    @error('password')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="form-group">
                    <div class="utility">
                        <div class="animated-checkbox">
                            <label><input type="checkbox" name="remember"><span class="label-text">Stay Signed in</span></label>
                        </div>
                    </div>
                </div>
                <div class="form-group btn-container">
                    <button class="btn btn-primary btn-block" type="submit">
                        <i class="fa fa-sign-in fa-lg fa-fw"></i> SIGN IN
                    </button>
                </div>
                <p class="semibold-text mb-0 text-center mt-3">
                    New member? <a href="{{ route('church.register') }}">Register now</a>
                </p>
                <p class="semibold-text mb-0 text-center mt-2">
                    Platform owner? <a href="{{ route('owner.login') }}">Owner login</a>
                </p>
            </form>
        </div>
    </section>
    <script src="{{ $vali }}/js/jquery-3.2.1.min.js"></script>
    <script src="{{ $vali }}/js/popper.min.js"></script>
    <script src="{{ $vali }}/js/bootstrap.min.js"></script>
    <script src="{{ $vali }}/js/main.js"></script>
    @include('partials.sweetalert-assets')
</body>
</html>
