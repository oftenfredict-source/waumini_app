@extends('layouts.church')

@section('title', $application->application_number)

@section('content')
@php
    $labels = [
        'membership_type' => __('members.fields.membership_type'),
        'member_type' => __('members.fields.member_type'),
        'full_name' => __('members.fields.full_name'),
        'gender' => __('members.fields.gender'),
        'date_of_birth' => __('members.fields.date_of_birth'),
        'education_level' => __('members.fields.education_level'),
        'profession' => __('members.fields.profession'),
        'nida_number' => __('members.fields.nida_number'),
        'phone_number' => __('members.fields.phone_number'),
        'email' => __('members.fields.email'),
        'region' => __('members.fields.origin_region'),
        'district' => __('members.fields.district'),
        'ward' => __('members.fields.ward'),
        'street' => __('members.fields.street'),
        'po_box' => __('members.fields.po_box'),
        'tribe' => __('members.fields.tribe'),
        'residence_region' => __('members.fields.region'),
        'residence_district' => __('pages.member_registrations.residence_district'),
        'residence_ward' => __('pages.member_registrations.residence_ward'),
        'residence_street' => __('pages.member_registrations.residence_street'),
        'residence_road' => __('pages.member_registrations.residence_road'),
        'residence_house_number' => __('members.fields.house_number'),
        'marital_status' => __('members.fields.marital_status'),
        'wedding_type' => __('members.fields.wedding_type'),
        'wedding_date' => __('members.fields.wedding_date'),
        'spouse_full_name' => __('members.fields.spouse_full_name'),
        'spouse_gender' => __('members.fields.spouse_gender'),
        'spouse_date_of_birth' => __('members.fields.spouse_dob'),
        'spouse_phone_number' => __('members.fields.spouse_phone'),
        'spouse_email' => __('pages.member_registrations.spouse_email'),
    ];
@endphp

@include('partials.page-header', [
    'icon' => 'fa fa-user-plus',
    'title' => $application->full_name,
    'subtitle' => $application->application_number,
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.registration_approvals'), 'route' => 'church.member-registrations.index'],
        ['label' => $application->application_number],
    ],
])

@include('partials.sweetalert-flash')

<div class="row">
    <div class="col-lg-8">
        <div class="tile mb-3">
            <div class="mb-3">
                <span class="badge badge-{{ $application->status->badgeClass() }}">{{ $application->status->label() }}</span>
            </div>
            <table class="table table-borderless table-sm">
                <tr><th width="180">{{ __('pages.shared.applicant') }}</th><td>{{ $application->full_name }}</td></tr>
                <tr><th>{{ __('common.phone') }}</th><td>{{ $application->phone_number ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.member_registrations.branch') }}</th><td>{{ $application->branch?->displayLabel() ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.submitted') }}</th><td>{{ $application->created_at?->format('M d, Y g:i A') }}</td></tr>
                @if($application->member)
                    <tr><th>{{ __('pages.member_registrations.member_record') }}</th><td><a href="{{ route('church.members.show', $application->member) }}">{{ $application->member->member_number }}</a></td></tr>
                @endif
                @if($application->assigned_envelope_number)
                    <tr><th>{{ __('pages.member_registrations.envelope') }}</th><td><code>{{ $application->assigned_envelope_number }}</code></td></tr>
                @endif
                @if($application->reviewer)
                    <tr><th>{{ __('pages.member_registrations.reviewed_by') }}</th><td>{{ $application->reviewer->name }} — {{ $application->reviewed_at?->format('M d, Y g:i A') }}</td></tr>
                @endif
                @if($application->rejection_reason)
                    <tr><th>{{ __('pages.member_registrations.rejection_reason') }}</th><td>{{ $application->rejection_reason }}</td></tr>
                @endif
            </table>

            @if($application->profilePictureUrl())
                <img src="{{ $application->profilePictureUrl() }}" alt="{{ __('pages.member_registrations.profile_photo_alt') }}" class="img-thumbnail mb-3" style="max-width: 160px;">
            @endif

            <h5 class="mt-3">{{ __('pages.member_registrations.registration_details') }}</h5>
            <table class="table table-sm table-striped">
                <tbody>
                    @foreach($labels as $key => $label)
                        @if(!empty($registrationData[$key]))
                            <tr>
                                <th width="200">{{ $label }}</th>
                                <td>{{ is_string($registrationData[$key]) ? ucfirst(str_replace('_', ' ', $registrationData[$key])) : $registrationData[$key] }}</td>
                            </tr>
                        @endif
                    @endforeach
                    @if(!empty($registrationData['is_baptized']))
                        <tr>
                            <th>{{ __('pages.member_registrations.baptized') }}</th>
                            <td>
                                {{ __('common.yes') }}
                                @if(!empty($registrationData['baptism_date']))
                                    — {{ $registrationData['baptism_date'] }}
                                @endif
                                @if(!empty($registrationData['baptism_place']))
                                    ({{ $registrationData['baptism_place'] }})
                                @endif
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>

            @if(count($dependants) > 0)
                <h5 class="mt-3">{{ __('pages.member_registrations.dependants') }}</h5>
                <ul>
                    @foreach($dependants as $dependant)
                        <li>{{ $dependant['full_name'] ?? '—' }} ({{ $dependant['relationship'] ?? '—' }})</li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        @can('review', $application)
            <div class="tile mb-3">
                <h5><i class="fa fa-check"></i> {{ __('pages.member_registrations.approve_title') }}</h5>
                <p class="text-muted small">{{ __('pages.member_registrations.approve_help') }}</p>
                <form method="POST" action="{{ route('church.member-registrations.approve', $application) }}" id="approveRegistrationForm">
                    @csrf
                    <div class="form-group">
                        <label>{{ __('pages.member_registrations.envelope_number') }} *</label>
                        <input type="text" name="envelope_number" id="approval_envelope_number" class="form-control"
                            maxlength="3" pattern="\d{3}" required>
                        <small id="approval_envelope_status" class="form-text"></small>
                    </div>
                    @if($needsSpouseEnvelope)
                        <div class="form-group">
                            <label>{{ __('pages.member_registrations.spouse_envelope_number') }} *</label>
                            <input type="text" name="spouse_envelope_number" id="approval_spouse_envelope_number" class="form-control"
                                maxlength="3" pattern="\d{3}" required>
                        </div>
                    @endif
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fa fa-check"></i> {{ __('pages.member_registrations.approve_button') }}
                    </button>
                </form>
            </div>

            <div class="tile">
                <h5><i class="fa fa-times"></i> {{ __('pages.member_registrations.reject_title') }}</h5>
                <form method="POST" action="{{ route('church.member-registrations.reject', $application) }}">
                    @csrf
                    <div class="form-group">
                        <label>{{ __('pages.member_registrations.reason_optional') }}</label>
                        <textarea name="rejection_reason" class="form-control" rows="3" placeholder="{{ __('pages.member_registrations.reason_placeholder') }}"></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger btn-block" onclick="return confirm(@json(__('pages.member_registrations.reject_confirm')))">
                        {{ __('pages.member_registrations.reject_button') }}
                    </button>
                </form>
            </div>
        @else
            <div class="tile">
                <p class="text-muted mb-0">{{ __('pages.member_registrations.already_reviewed') }}</p>
                @if($application->status->value === 'approved' && $application->member)
                    <a href="{{ route('church.members.show', $application->member) }}" class="btn btn-primary btn-block mt-3">{{ __('pages.member_registrations.view_member_profile') }}</a>
                @endif
            </div>
        @endcan
    </div>
</div>
@endsection

@push('scripts')
@can('review', $application)
<script>
(function () {
    const input = document.getElementById('approval_envelope_number');
    const status = document.getElementById('approval_envelope_status');
    const url = @json(route('church.member-registrations.check-envelope'));
    if (!input) return;

    let timer;
    input.addEventListener('input', function () {
        clearTimeout(timer);
        const value = input.value.trim();
        if (value.length !== 3) {
            status.textContent = '';
            return;
        }
        timer = setTimeout(function () {
            fetch(url + '?envelope=' + encodeURIComponent(value), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => r.json())
                .then(data => {
                    status.textContent = data.message || '';
                    status.className = 'form-text ' + (data.available ? 'text-success' : 'text-danger');
                });
        }, 300);
    });
})();
</script>
@endcan
@endpush
