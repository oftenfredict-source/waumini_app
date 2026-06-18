@php $vali = asset('vali-master/docs'); @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Owner Login - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ $vali }}/css/main.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    @include('partials.brand-styles')
</head>
<body>
    <section class="material-half-bg"><div class="cover"></div></section>
    <section class="login-content">
        <div class="logo"><h1>{{ config('app.name') }}</h1></div>
        <div class="login-box">
            <form class="login-form" method="POST" action="{{ route('owner.login.submit') }}">
                @csrf
                <h3 class="login-head"><i class="fa fa-lg fa-fw fa-user"></i> OWNER SIGN IN</h3>

                @include('partials.sweetalert-flash')

                <div class="form-group">
                    <label class="control-label">EMAIL</label>
                    <input class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email') }}" placeholder="admin@example.com" autofocus required>
                    @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="form-group">
                    <label class="control-label">PASSWORD</label>
                    <input class="form-control @error('password') is-invalid @enderror" type="password" name="password" placeholder="Password" required>
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
