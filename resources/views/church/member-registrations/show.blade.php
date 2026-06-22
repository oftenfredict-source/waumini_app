@extends('layouts.church')

@section('title', $application->application_number)

@section('content')
@php
    $labels = [
        'membership_type' => 'Membership Type',
        'member_type' => 'Member Type',
        'full_name' => 'Full Name',
        'gender' => 'Gender',
        'date_of_birth' => 'Date of Birth',
        'education_level' => 'Education Level',
        'profession' => 'Profession',
        'nida_number' => 'NIDA Number',
        'phone_number' => 'Phone',
        'email' => 'Email',
        'region' => 'Region',
        'district' => 'District',
        'ward' => 'Ward',
        'street' => 'Street',
        'po_box' => 'P.O. Box',
        'tribe' => 'Tribe',
        'residence_region' => 'Residence Region',
        'residence_district' => 'Residence District',
        'residence_ward' => 'Residence Ward',
        'residence_street' => 'Residence Street',
        'residence_road' => 'Residence Road',
        'residence_house_number' => 'House Number',
        'marital_status' => 'Marital Status',
        'wedding_type' => 'Wedding Type',
        'wedding_date' => 'Wedding Date',
        'spouse_full_name' => 'Spouse Name',
        'spouse_gender' => 'Spouse Gender',
        'spouse_date_of_birth' => 'Spouse Date of Birth',
        'spouse_phone_number' => 'Spouse Phone',
        'spouse_email' => 'Spouse Email',
    ];
@endphp

<div class="app-title">
    <div>
        <h1><i class="fa fa-user-plus"></i> {{ $application->full_name }}</h1>
        <p><code>{{ $application->application_number }}</code></p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.member-registrations.index') }}">Registration Approvals</a></li>
        <li class="breadcrumb-item">{{ $application->application_number }}</li>
    </ul>
</div>

@include('partials.sweetalert-flash')

<div class="row">
    <div class="col-lg-8">
        <div class="tile mb-3">
            <div class="mb-3">
                <span class="badge badge-{{ $application->status->badgeClass() }}">{{ $application->status->label() }}</span>
            </div>
            <table class="table table-borderless table-sm">
                <tr><th width="180">Applicant</th><td>{{ $application->full_name }}</td></tr>
                <tr><th>Phone</th><td>{{ $application->phone_number ?? '—' }}</td></tr>
                <tr><th>Branch</th><td>{{ $application->branch?->displayLabel() ?? '—' }}</td></tr>
                <tr><th>Submitted</th><td>{{ $application->created_at?->format('M d, Y g:i A') }}</td></tr>
                @if($application->member)
                    <tr><th>Member Record</th><td><a href="{{ route('church.members.show', $application->member) }}">{{ $application->member->member_number }}</a></td></tr>
                @endif
                @if($application->assigned_envelope_number)
                    <tr><th>Envelope</th><td><code>{{ $application->assigned_envelope_number }}</code></td></tr>
                @endif
                @if($application->reviewer)
                    <tr><th>Reviewed By</th><td>{{ $application->reviewer->name }} — {{ $application->reviewed_at?->format('M d, Y g:i A') }}</td></tr>
                @endif
                @if($application->rejection_reason)
                    <tr><th>Rejection Reason</th><td>{{ $application->rejection_reason }}</td></tr>
                @endif
            </table>

            @if($application->profilePictureUrl())
                <img src="{{ $application->profilePictureUrl() }}" alt="Profile photo" class="img-thumbnail mb-3" style="max-width: 160px;">
            @endif

            <h5 class="mt-3">Registration Details</h5>
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
                            <th>Baptized</th>
                            <td>
                                Yes
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
                <h5 class="mt-3">Dependants</h5>
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
                <h5><i class="fa fa-check"></i> Approve Registration</h5>
                <p class="text-muted small">Assign an envelope number and create the member login account.</p>
                <form method="POST" action="{{ route('church.member-registrations.approve', $application) }}" id="approveRegistrationForm">
                    @csrf
                    <div class="form-group">
                        <label>Envelope Number *</label>
                        <input type="text" name="envelope_number" id="approval_envelope_number" class="form-control"
                            maxlength="3" pattern="\d{3}" required>
                        <small id="approval_envelope_status" class="form-text"></small>
                    </div>
                    @if($needsSpouseEnvelope)
                        <div class="form-group">
                            <label>Spouse Envelope Number *</label>
                            <input type="text" name="spouse_envelope_number" id="approval_spouse_envelope_number" class="form-control"
                                maxlength="3" pattern="\d{3}" required>
                        </div>
                    @endif
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fa fa-check"></i> Approve & Create Member
                    </button>
                </form>
            </div>

            <div class="tile">
                <h5><i class="fa fa-times"></i> Reject Registration</h5>
                <form method="POST" action="{{ route('church.member-registrations.reject', $application) }}">
                    @csrf
                    <div class="form-group">
                        <label>Reason (optional)</label>
                        <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Reason for rejection"></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('Reject this registration application?')">
                        Reject Application
                    </button>
                </form>
            </div>
        @else
            <div class="tile">
                <p class="text-muted mb-0">This application has already been reviewed.</p>
                @if($application->status->value === 'approved' && $application->member)
                    <a href="{{ route('church.members.show', $application->member) }}" class="btn btn-primary btn-block mt-3">View Member Profile</a>
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
