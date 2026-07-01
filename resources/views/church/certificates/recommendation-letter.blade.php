@extends('church.certificates.layout')

@section('certificate-body')
    <div class="doc-title">{{ __('certificates.recommendation_title') }}</div>

    <div class="ref-line">
        {{ __('certificates.number') }} <strong>{{ $memberRequest->reference_number }}</strong><br>
        {{ __('certificates.date') }} <strong>{{ $issuedAtFormatted }}</strong>
    </div>

    <div class="content">
        <p>{{ __('certificates.to_whom') }}</p>

        <p>
            {!! __('certificates.recommendation_intro', [
                'church' => e($displayName ?? $church->name),
                'name' => e($member->full_name),
                'number' => e($member->member_number),
            ]) !!}
        </p>

        <p>
            <strong>{{ __('certificates.subject') }}</strong> {{ $memberRequest->subject }}
        </p>

        <p>
            <strong>{{ __('certificates.description') }}</strong><br>
            {{ $memberRequest->description }}
        </p>

        @if($memberRequest->response)
            <p>
                <strong>{{ __('certificates.church_comments') }}</strong><br>
                {{ $memberRequest->response }}
            </p>
        @endif

        <p>
            {{ __('certificates.recommendation_closing') }}
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
