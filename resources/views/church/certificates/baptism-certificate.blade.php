@extends('church.certificates.layout')

@section('certificate-body')
    <div class="doc-title">{{ __('certificates.baptism_title') }}</div>

    <div class="ref-line">
        {{ __('certificates.number') }} <strong>{{ $memberRequest->reference_number }}</strong><br>
        {{ __('certificates.date') }} <strong>{{ $issuedAtFormatted }}</strong>
    </div>

    <div class="content">
        <p style="text-align:center; font-size:14px; margin-bottom:18px;">
            {{ __('certificates.baptism_confirm') }}
        </p>

        <p style="text-align:center; font-size:20px; font-weight:bold; color:#940000; margin-bottom:18px;">
            {{ $member->full_name }}
        </p>

        <p style="text-align:center;">
            {{ __('certificates.member_number') }} {{ $member->member_number }}
        </p>

        <p>
            {!! __('certificates.baptism_body', ['church' => '<strong>'.e($displayName ?? $church->name).'</strong>']) !!}
        </p>

        @if($memberRequest->description)
            <p>
                <strong>{{ __('certificates.description') }}</strong><br>
                {{ $memberRequest->description }}
            </p>
        @endif

        <p>
            {{ __('certificates.baptism_closing') }}
        </p>
    </div>

    <div class="signature">
        <div class="signature-line">
            <strong>{{ $signatoryName }}</strong><br>
            {{ $signatoryTitle }}<br>
            {{ $church->name }}
        </div>
    </div>
@endsection
