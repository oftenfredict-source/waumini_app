@php
    $type = $memberRequest->type->value;
    $brand = $brandColor ?? '#940000';
    $title = match ($type) {
        'baptism_certificate' => __('certificates.baptism_title'),
        'travelling_certificate' => __('certificates.travelling_title'),
        'recommendation_letter' => __('certificates.recommendation_title'),
        default => __('certificates.default_title'),
    };
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 0; size: A4 portrait; }
        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            color: #222;
            font-size: 12px;
            line-height: 1.55;
        }
        #watermark {
            position: fixed;
            top: 0;
            left: 0;
            width: 210mm;
            height: 297mm;
            z-index: -1000;
        }
        #watermark img {
            width: 210mm;
            height: 297mm;
        }
        .sheet {
            padding: 28mm 22mm 26mm 22mm;
        }
        .panel {
            background: #ffffff;
            border: 2px solid {{ $brand }};
            padding: 14mm 12mm 12mm 12mm;
            min-height: 220mm;
        }
        .panel-inner {
            border: 1px solid {{ $brand }};
            padding: 10mm 9mm 9mm 9mm;
            min-height: 198mm;
        }
        .header {
            text-align: center;
            margin-bottom: 8mm;
            padding-bottom: 5mm;
            border-bottom: 2px solid {{ $brand }};
        }
        .church-logo {
            max-height: 58px;
            max-width: 140px;
            margin-bottom: 6px;
        }
        .church-name {
            color: {{ $brand }};
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0 0 3px;
        }
        .church-meta {
            font-size: 10px;
            color: #555;
        }
        .doc-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            color: {{ $brand }};
            letter-spacing: 1px;
            text-transform: uppercase;
            margin: 6mm 0 5mm;
        }
        .ref-line {
            text-align: right;
            font-size: 10px;
            margin-bottom: 5mm;
            color: #444;
        }
        .content p {
            margin: 0 0 3.5mm;
            text-align: justify;
        }
        .hero-name {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            color: {{ $brand }};
            margin: 5mm 0;
            padding: 3mm 0;
            border-top: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
        }
        .lead {
            text-align: center;
            font-size: 13px;
            margin-bottom: 4mm;
        }
        .member-box {
            border: 1px solid #d8d8d8;
            background: #fafafa;
            padding: 4mm 5mm;
            margin: 4mm 0 5mm;
        }
        .member-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .member-box th {
            text-align: left;
            width: 40%;
            padding: 2mm 0;
            color: #555;
            font-weight: normal;
            font-size: 11px;
        }
        .member-box td {
            padding: 2mm 0;
            font-weight: bold;
            font-size: 12px;
        }
        .signature {
            margin-top: 10mm;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        .signature-image {
            max-height: 48px;
            max-width: 160px;
            display: block;
            margin-bottom: 2px;
        }
        .seal-image {
            max-height: 80px;
            max-width: 80px;
            display: block;
            margin: 0 auto 2px;
        }
        .seal-caption {
            font-size: 9px;
            color: #555;
            text-align: center;
        }
        .signature-line {
            width: 210px;
            border-top: 1px solid #333;
            padding-top: 3px;
            margin-top: 3px;
            font-size: 11px;
        }
        .footer {
            margin-top: 8mm;
            font-size: 9px;
            color: #777;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 3mm;
        }
        .stamp {
            color: {{ $brand }};
            font-size: 9px;
            font-weight: bold;
            margin-top: 2mm;
        }
    </style>
</head>
<body>
    @if(!empty($templateBackgroundBase64))
        <div id="watermark">
            <img src="{{ $templateBackgroundBase64 }}" alt="">
        </div>
    @endif

    <div class="sheet">
        <div class="panel">
            <div class="panel-inner">
                <div class="header">
                    @if(!empty($churchLogoBase64))
                        <img src="{{ $churchLogoBase64 }}" alt="" class="church-logo">
                    @endif
                    <div class="church-name">{{ $displayName ?? $church->name }}</div>
                    <div class="church-meta">
                        @if($church->denomination){{ $church->denomination }} &bull; @endif
                        @if(!empty($displayAddress) || !empty($displayCity))
                            {{ $displayAddress ?? '' }}@if(!empty($displayCity)), {{ $displayCity }}@endif
                        @endif
                        @if(!empty($displayPhone)) &bull; {{ __('certificates.phone') }} {{ $displayPhone }}@endif
                        @if(!empty($displayEmail)) &bull; {{ $displayEmail }}@endif
                    </div>
                </div>

                <div class="doc-title">{{ $title }}</div>

                <div class="ref-line">
                    {{ __('certificates.number') }} <strong>{{ $memberRequest->reference_number }}</strong><br>
                    {{ __('certificates.date') }} <strong>{{ $issuedAtFormatted }}</strong>
                </div>

                <div class="content">
                    @if($type === 'baptism_certificate')
                        <p class="lead">{{ __('certificates.baptism_confirm') }}</p>
                        <div class="hero-name">{{ $member->full_name }}</div>
                        <div class="member-box">
                            <table>
                                <tr>
                                    <th>{{ __('certificates.member_number') }}</th>
                                    <td>{{ $member->member_number }}</td>
                                </tr>
                                @if($membershipDateFormatted)
                                    <tr>
                                        <th>{{ __('certificates.member_since') }}</th>
                                        <td>{{ $membershipDateFormatted }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                        <p>{!! __('certificates.baptism_body', ['church' => '<strong>'.e($displayName ?? $church->name).'</strong>']) !!}</p>
                        @if($memberRequest->description)
                            <p><strong>{{ __('certificates.description') }}</strong><br>{{ $memberRequest->description }}</p>
                        @endif
                        <p>{{ __('certificates.baptism_closing') }}</p>

                    @elseif($type === 'travelling_certificate')
                        <p>{{ __('certificates.to_whom') }}</p>
                        <p>{!! __('certificates.travelling_intro', ['church' => e($displayName ?? $church->name)]) !!}</p>
                        <div class="hero-name">{{ $member->full_name }}</div>
                        <div class="member-box">
                            <table>
                                <tr>
                                    <th>{{ __('certificates.full_name') }}</th>
                                    <td>{{ $member->full_name }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('certificates.member_number') }}</th>
                                    <td>{{ $member->member_number }}</td>
                                </tr>
                                @if($member->phone_number)
                                    <tr>
                                        <th>{{ __('certificates.phone_number') }}</th>
                                        <td>{{ $member->phone_number }}</td>
                                    </tr>
                                @endif
                                @if($membershipDateFormatted)
                                    <tr>
                                        <th>{{ __('certificates.member_since') }}</th>
                                        <td>{{ $membershipDateFormatted }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <th>{{ __('certificates.membership_status') }}</th>
                                    <td>{{ $membershipStatus }}</td>
                                </tr>
                            </table>
                        </div>
                        <p>
                            <strong>{{ __('certificates.travel_reason') }}</strong><br>
                            {{ $memberRequest->subject }}@if($memberRequest->description) — {{ $memberRequest->description }}@endif
                        </p>
                        <p>{{ __('certificates.travelling_recommend', ['name' => $member->full_name]) }}</p>
                        <p>{{ __('certificates.god_bless') }}</p>

                    @else
                        <p>{{ __('certificates.to_whom') }}</p>
                        <p>
                            {!! __('certificates.recommendation_intro', [
                                'church' => e($displayName ?? $church->name),
                                'name' => e($member->full_name),
                                'number' => e($member->member_number),
                            ]) !!}
                        </p>
                        <div class="hero-name">{{ $member->full_name }}</div>
                        <div class="member-box">
                            <table>
                                <tr>
                                    <th>{{ __('certificates.member_number') }}</th>
                                    <td>{{ $member->member_number }}</td>
                                </tr>
                                @if($member->phone_number)
                                    <tr>
                                        <th>{{ __('certificates.phone_number') }}</th>
                                        <td>{{ $member->phone_number }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                        <p><strong>{{ __('certificates.subject') }}</strong> {{ $memberRequest->subject }}</p>
                        <p><strong>{{ __('certificates.description') }}</strong><br>{{ $memberRequest->description }}</p>
                        @if($memberRequest->response)
                            <p><strong>{{ __('certificates.church_comments') }}</strong><br>{{ $memberRequest->response }}</p>
                        @endif
                        <p>{{ __('certificates.recommendation_closing') }}</p>
                    @endif
                </div>

                @include('church.certificates.partials.signature-block')

                <div class="footer">
                    {{ __('certificates.issued_via') }} &bull; {{ $memberRequest->reference_number }}
                    <div class="stamp">{{ __('certificates.official_document') }}</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
