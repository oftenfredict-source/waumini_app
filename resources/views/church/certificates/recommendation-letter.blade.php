@extends('church.certificates.layout')

@section('certificate-body')
    <div class="doc-title">Barua ya Mapendekezo</div>

    <div class="ref-line">
        Nambari: <strong>{{ $memberRequest->reference_number }}</strong><br>
        Tarehe: <strong>{{ $issuedAtFormatted }}</strong>
    </div>

    <div class="content">
        <p>Kwa Wote Wanaohusika,</p>

        <p>
            Sisi, uongozi wa <strong>{{ $displayName ?? $church->name }}</strong>, tunampendekeza mwanachama wetu
            <strong>{{ $member->full_name }}</strong> (Nambari ya Mwanachama: {{ $member->member_number }})
            kulingana na ujuzi wetu kuhusu tabia yake, mwenendo wake, na ushiriki wake katika maisha ya kanisa.
        </p>

        <p>
            <strong>Mada:</strong> {{ $memberRequest->subject }}
        </p>

        <p>
            <strong>Maelezo:</strong><br>
            {{ $memberRequest->description }}
        </p>

        @if($memberRequest->response)
            <p>
                <strong>Maoni ya kanisa:</strong><br>
                {{ $memberRequest->response }}
            </p>
        @endif

        <p>
            Tunaamini mapendekezo haya yatapokelewa kwa imani njema. Kwa uthibitisho wowote,
            tafadhali wasiliana na ofisi ya kanisa kwa kutumia maelezo yaliyoonyeshwa hapo juu.
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
