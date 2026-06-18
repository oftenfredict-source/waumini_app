@extends('church.certificates.layout')

@section('certificate-body')
    <div class="doc-title">Barua ya Kusafiri</div>

    <div class="ref-line">
        Nambari: <strong>{{ $memberRequest->reference_number }}</strong><br>
        Tarehe: <strong>{{ $issuedAtFormatted }}</strong>
    </div>

    <div class="content">
        <p>Kwa Wote Wanaohusika,</p>

        <p>
            Tunathibitisha kwamba mtu aliyeandikwa hapa chini ni <strong>mwanachama halali</strong> wa
            <strong>{{ $displayName ?? $church->name }}</strong> na anajulikana kwetu kama mtu wa tabia njema ya Kikristo
            na mshiriki mwaminifu katika jumuiya yetu ya waumini.
        </p>

        <div class="member-box">
            <table>
                <tr>
                    <th>Jina Kamili</th>
                    <td>{{ $member->full_name }}</td>
                </tr>
                <tr>
                    <th>Nambari ya Mwanachama</th>
                    <td>{{ $member->member_number }}</td>
                </tr>
                @if($member->phone_number)
                    <tr>
                        <th>Nambari ya Simu</th>
                        <td>{{ $member->phone_number }}</td>
                    </tr>
                @endif
                @if($membershipDateFormatted)
                    <tr>
                        <th>Mwanachama Tangu</th>
                        <td>{{ $membershipDateFormatted }}</td>
                    </tr>
                @endif
                <tr>
                    <th>Hali ya Uanachama</th>
                    <td>{{ $membershipStatus }}</td>
                </tr>
            </table>
        </div>

        <p>
            <strong>Sababu ya safari:</strong><br>
            {{ $memberRequest->subject }} — {{ $memberRequest->description }}
        </p>

        <p>
            Tunampendekeza {{ $member->full_name }} kwa ushirika wa waumini na viongozi wa makanisa
            popote atakaposafiri, na tunaomba apokelewe kwa upendo na msaada wa Kikristo.
        </p>

        <p>Mungu awabariki safari yake.</p>
    </div>

    <div class="signature">
        <div class="signature-line">
            <strong>{{ $signatoryName }}</strong><br>
            {{ $signatoryTitle }}<br>
            {{ $church->name }}
        </div>
    </div>
@endsection
