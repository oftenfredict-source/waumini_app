@php
    $checkInUrl = \App\Services\Church\AttendanceService::scanUrl($sourceType, (int) $sourceId);
    $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=420x420&ecc=M&data='.urlencode($checkInUrl);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('pages.attendance.scan_qr_title') }} — {{ $label }}</title>
    <link rel="stylesheet" href="{{ \App\Support\WauminiBrand::publicAsset('vali-master/docs') }}/css/main.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body { background: #fff; }
        .poster { max-width: 640px; margin: 2rem auto; text-align: center; padding: 2rem; }
        .poster h1 { font-size: 1.75rem; margin-bottom: .5rem; }
        .poster .meta { color: #555; margin-bottom: 1.5rem; }
        .poster img { width: 420px; height: 420px; background: #fff; padding: 12px; border: 1px solid #eee; border-radius: 16px; }
        .poster .help { margin-top: 1.25rem; font-size: 1.05rem; }
        @media print {
            .no-print { display: none !important; }
            .poster { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="poster">
        <p class="no-print mb-3">
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="fa fa-print"></i> {{ __('pages.attendance.print_qr') }}
            </button>
            <a href="{{ url()->previous() }}" class="btn btn-secondary">{{ __('common.back') }}</a>
        </p>
        <h1>{{ __('pages.attendance.scan_qr_title') }}</h1>
        <p class="meta">{{ $label }}</p>
        <img src="{{ $qrUrl }}" alt="Attendance QR" width="420" height="420">
        <p class="help">{{ __('pages.attendance.scan_qr_help') }}</p>
    </div>
</body>
</html>
