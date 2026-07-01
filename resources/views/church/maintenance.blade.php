@php $vali = asset('vali-master/docs'); @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('common.maintenance_title') }} - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ $vali }}/css/main.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    @include('partials.brand-styles')
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #f5f6fa;
            padding: 1.5rem;
        }
        .maintenance-locale {
            position: fixed;
            top: 1rem;
            right: 1rem;
        }
        .maintenance-card {
            max-width: 520px;
            width: 100%;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.08);
            padding: 2.5rem 2rem;
            text-align: center;
        }
        .maintenance-icon {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(148, 0, 0, 0.1);
            color: var(--waumini-primary, #940000);
            font-size: 2rem;
            margin-bottom: 1.25rem;
        }
        .maintenance-card h1 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #2a2c36;
        }
        .maintenance-card p {
            color: #5c6873;
            line-height: 1.7;
            margin-bottom: 0;
            white-space: pre-line;
        }
    </style>
</head>
<body>
    <div class="maintenance-locale">
        @include('partials.locale-switcher', ['variant' => 'links', 'class' => 'maintenance-locale-switcher'])
    </div>
    <div class="maintenance-card">
        <div class="maintenance-icon">
            <i class="fa fa-wrench"></i>
        </div>
        <h1>{{ __('common.maintenance_heading') }}</h1>
        <p>{{ $message }}</p>
    </div>
</body>
</html>
