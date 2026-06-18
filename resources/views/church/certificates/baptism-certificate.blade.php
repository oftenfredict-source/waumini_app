@extends('church.certificates.layout')

@section('certificate-body')
    <div class="doc-title">Cheti cha Ubatizo</div>

    <div class="ref-line">
        Nambari: <strong>{{ $memberRequest->reference_number }}</strong><br>
        Tarehe: <strong>{{ $issuedAtFormatted }}</strong>
    </div>

    <div class="content">
        <p style="text-align:center; font-size:14px; margin-bottom:18px;">
            Tunathibitisha kwamba
        </p>

        <p style="text-align:center; font-size:20px; font-weight:bold; color:#940000; margin-bottom:18px;">
            {{ $member->full_name }}
        </p>

        <p style="text-align:center;">
            Nambari ya Mwanachama: {{ $member->member_number }}
        </p>

        <p>
            amepokelewa kama mwanachama aliyebatizwa katika <strong>{{ $displayName ?? $church->name }}</strong>, kwa mujibu
            wa imani na desturi za kanisa letu.
        </p>

        @if($memberRequest->description)
            <p>
                <strong>Maelezo:</strong><br>
                {{ $memberRequest->description }}
            </p>
        @endif

        <p>
            Tunaomba aendelee kukua katika imani, tumaini, na upendo katika Bwana wetu Yesu Kristo.
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
