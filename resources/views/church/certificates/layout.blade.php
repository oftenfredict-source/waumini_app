<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? __('certificates.default_title') }}</title>
    <style>
        @page { margin: 36px 42px; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #222;
            font-size: 12px;
            line-height: 1.55;
        }
        .church-logo {
            max-height: 70px;
            max-width: 160px;
            object-fit: contain;
            margin-bottom: 10px;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #940000;
            padding-bottom: 14px;
            margin-bottom: 22px;
        }
        .church-name {
            color: #940000;
            font-size: 22px;
            font-weight: bold;
            margin: 0 0 4px;
            text-transform: uppercase;
        }
        .church-meta {
            font-size: 11px;
            color: #555;
        }
        .doc-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            color: #940000;
            margin: 18px 0 20px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .ref-line {
            text-align: right;
            font-size: 11px;
            margin-bottom: 16px;
        }
        .content p {
            margin: 0 0 12px;
            text-align: justify;
        }
        .member-box {
            border: 1px solid #ddd;
            background: #fafafa;
            padding: 12px 14px;
            margin: 16px 0;
        }
        .member-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .member-box th {
            text-align: left;
            width: 38%;
            padding: 4px 0;
            color: #555;
            font-weight: normal;
        }
        .member-box td {
            padding: 4px 0;
            font-weight: bold;
        }
        .signature {
            margin-top: 42px;
        }
        .signature-line {
            width: 240px;
            border-top: 1px solid #333;
            padding-top: 6px;
            margin-top: 48px;
        }
        .footer {
            margin-top: 28px;
            font-size: 10px;
            color: #777;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        .stamp {
            color: #940000;
            font-size: 10px;
            font-weight: bold;
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        @if(!empty($churchLogoBase64))
            <img src="{{ $churchLogoBase64 }}" alt="{{ $displayName ?? $church->name }}" class="church-logo">
        @endif
        <div class="church-name">{{ $displayName ?? $church->name }}</div>
        <div class="church-meta">
            @if($church->denomination){{ $church->denomination }} &bull; @endif
            @if(!empty($displayAddress) || !empty($displayCity)){{ $displayAddress ?? '' }}@if(!empty($displayCity)), {{ $displayCity }}@endif &bull; @endif
            @if(!empty($displayPhone)){{ __('certificates.phone') }} {{ $displayPhone }}@endif
            @if(!empty($displayEmail)) &bull; {{ $displayEmail }}@endif
        </div>
    </div>

    @yield('certificate-body')

    <div class="footer">
        {{ __('certificates.issued_via') }} &bull; {{ $memberRequest->reference_number }}
        <div class="stamp">{{ __('certificates.official_document') }}</div>
    </div>
</body>
</html>
