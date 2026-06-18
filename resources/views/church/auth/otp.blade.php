@php $vali = asset('vali-master/docs'); @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Code - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ $vali }}/css/main.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    @include('partials.brand-styles')
</head>
<body>
    <section class="material-half-bg"><div class="cover"></div></section>
    <section class="login-content">
        <div class="logo"><h1>{{ config('app.name') }}</h1></div>
        <div class="login-box">
            <form class="login-form" method="POST" action="{{ route('church.login.otp.verify') }}">
                @csrf
                <h3 class="login-head"><i class="fa fa-lg fa-fw fa-mobile"></i> VERIFY CODE</h3>

                @include('partials.sweetalert-flash')

                <p class="text-muted text-center mb-3">
                    Enter the 6-digit code sent to the phone linked to
                    <strong>{{ $loginIdentifier }}</strong>.
                </p>

                @if($otpExpiresAt)
                    <p class="text-center small text-muted mb-3">
                        Code expires at {{ $otpExpiresAt->format('H:i') }}
                    </p>
                @endif

                <div class="form-group">
                    <label class="control-label">VERIFICATION CODE</label>
                    <input class="form-control @error('otp') is-invalid @enderror text-center"
                           type="text" name="otp" maxlength="6" pattern="[0-9]{6}"
                           inputmode="numeric" autocomplete="one-time-code" autofocus required
                           placeholder="000000">
                    @error('otp')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="form-group btn-container">
                    <button class="btn btn-primary btn-block" type="submit">
                        <i class="fa fa-check fa-lg fa-fw"></i> VERIFY &amp; SIGN IN
                    </button>
                </div>

                <p class="semibold-text mb-0 text-center mt-3">
                    <a href="{{ route('church.login') }}">Back to sign in</a>
                </p>
            </form>

            @if($resendAttempts < $maxResendAttempts)
                <form method="POST" action="{{ route('church.login.otp.resend') }}" class="text-center mt-3">
                    @csrf
                    <button type="submit" class="btn btn-link p-0">Resend code</button>
                    <small class="text-muted d-block">
                        {{ $maxResendAttempts - $resendAttempts }} resend(s) remaining
                    </small>
                </form>
            @endif
        </div>
    </section>
    <script src="{{ $vali }}/js/jquery-3.2.1.min.js"></script>
    <script src="{{ $vali }}/js/popper.min.js"></script>
    <script src="{{ $vali }}/js/bootstrap.min.js"></script>
    <script src="{{ $vali }}/js/main.js"></script>
    @include('partials.sweetalert-assets')
</body>
</html>
