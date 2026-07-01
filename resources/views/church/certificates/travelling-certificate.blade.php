@extends('church.certificates.layout')

@section('certificate-body')
    <div class="doc-title">{{ __('certificates.travelling_title') }}</div>

    <div class="ref-line">
        {{ __('certificates.number') }} <strong>{{ $memberRequest->reference_number }}</strong><br>
        {{ __('certificates.date') }} <strong>{{ $issuedAtFormatted }}</strong>
    </div>

    <div class="content">
        <p>{{ __('certificates.to_whom') }}</p>

        <p>
            {!! __('certificates.travelling_intro', ['church' => e($displayName ?? $church->name)]) !!}
        </p>

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
            {{ $memberRequest->subject }} — {{ $memberRequest->description }}
        </p>

        <p>
            {{ __('certificates.travelling_recommend', ['name' => $member->full_name]) }}
        </p>

        <p>{{ __('certificates.god_bless') }}</p>
    </div>

    <div class="signature">
        <div class="signature-line">
            <strong>{{ $signatoryName }}</strong><br>
            {{ $signatoryTitle }}<br>
            {{ $church->name }}
        </div>
    </div>
@endsection
