@extends('layouts.church')

@section('title', $member->full_name)

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-user"></i> {{ $member->full_name }}</h1>
        <p>Member profile</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.members.index') }}">Members</a></li>
        <li class="breadcrumb-item">{{ $member->full_name }}</li>
    </ul>
</div>

@if($member->isArchived())
    <div class="alert alert-warning">
        <h5 class="alert-heading"><i class="fa fa-archive"></i> Archived Member</h5>
        <p class="mb-1"><strong>Archived:</strong> {{ $member->archived_at?->format('M d, Y H:i') ?? '—' }}</p>
        <p class="mb-1"><strong>By:</strong> {{ $member->archivedBy?->name ?? '—' }}</p>
        <p class="mb-0"><strong>Reason:</strong> {{ $member->archive_reason ?? '—' }}</p>
    </div>
@endif

<div class="row">
    <div class="col-md-8">
        <div class="tile">
            <div class="d-flex align-items-start">
                @if($member->profilePictureUrl())
                    <img src="{{ $member->profilePictureUrl() }}" alt="{{ $member->full_name }}" class="profile-preview mr-3">
                @endif
                <div class="flex-grow-1">
                    <h3 class="tile-title">Personal Information</h3>
                    <table class="table table-borderless table-sm">
                        <tr><th width="180">Member ID</th><td><code>{{ $member->member_number }}</code></td></tr>
                        <tr><th>Envelope Number</th><td><code>{{ $member->envelope_number ?? '—' }}</code></td></tr>
                        <tr><th>Membership Type</th><td>{{ ucfirst($member->membership_type->value) }}</td></tr>
                        @if($member->isTemporary())
                            <tr><th>Stay Duration</th><td>{{ $member->temporaryDurationLabel() ?? '—' }}</td></tr>
                            <tr><th>Expires On</th>
                                <td>
                                    {{ $member->membership_expires_at?->format('M d, Y') ?? '—' }}
                                    @if($member->membership_expires_at)
                                        @if($member->isMembershipExpired())
                                            <span class="badge badge-danger">Expired</span>
                                        @else
                                            <span class="badge badge-info">{{ $member->membershipDaysRemaining() }} days left</span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endif
                        <tr><th>Member Type</th><td>{{ $member->member_type?->label() ?? '—' }}</td></tr>
                        <tr><th>Gender</th><td>{{ $member->gender ? ucfirst($member->gender) : '—' }}</td></tr>
                        <tr><th>Date of Birth</th><td>{{ $member->date_of_birth?->format('M d, Y') ?? '—' }}</td></tr>
                        <tr><th>Education</th><td>{{ $member->education_level?->label() ?? '—' }}</td></tr>
                        <tr><th>Profession</th><td>{{ $member->profession ?? '—' }}</td></tr>
                        <tr><th>NIDA</th><td>{{ $member->nida_number ?? '—' }}</td></tr>
                        <tr><th>Baptism</th>
                            <td>
                                @if($member->is_baptized)
                                    <span class="badge badge-info">Baptized</span>
                                    @if($member->baptism_date)
                                        <div class="small text-muted mt-1">Date: {{ $member->baptism_date->format('M d, Y') }}</div>
                                    @endif
                                    @if($member->baptism_place)
                                        <div class="small text-muted">Place: {{ $member->baptism_place }}</div>
                                    @endif
                                    @if($member->baptized_by)
                                        <div class="small text-muted">By: {{ $member->baptized_by }}</div>
                                    @endif
                                @else
                                    <span class="text-muted">Not baptized / not recorded</span>
                                @endif
                            </td>
                        </tr>
                        <tr><th>Status</th>
                            <td>
                                @if($member->isArchived())
                                    <span class="badge badge-secondary">Archived</span>
                                @else
                                    <span class="badge badge-success">Active</span>
                                @endif
                            </td>
                        </tr>
                        @if($member->user)
                            <tr><th>Login Username</th><td><code>{{ $member->member_number }}</code></td></tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <div class="tile">
            <h3 class="tile-title">Contact & Origin</h3>
            <table class="table table-borderless table-sm">
                <tr><th width="180">Phone</th><td>{{ $member->phone_number }}</td></tr>
                <tr><th>Email</th><td>{{ $member->email ?? '—' }}</td></tr>
                <tr><th>Origin</th><td>{{ collect([$member->region, $member->district, $member->ward, $member->street])->filter()->implode(', ') ?: '—' }}</td></tr>
                <tr><th>P.O. Box</th><td>{{ $member->po_box ?? '—' }}</td></tr>
                <tr><th>Tribe</th><td>{{ $member->other_tribe ?: ($member->tribe && $member->tribe !== 'Other' ? $member->tribe : '—') }}</td></tr>
            </table>
        </div>

        <div class="tile">
            <h3 class="tile-title">Residence</h3>
            <table class="table table-borderless table-sm">
                <tr><th width="180">Location</th><td>{{ collect([$member->residence_region, $member->residence_district, $member->residence_ward])->filter()->implode(', ') ?: '—' }}</td></tr>
                <tr><th>Street / Road</th><td>{{ collect([$member->residence_street, $member->residence_road])->filter()->implode(', ') ?: '—' }}</td></tr>
                <tr><th>House Number</th><td>{{ $member->residence_house_number ?? '—' }}</td></tr>
            </table>
        </div>

        @php $spouse = $member->resolvedSpouse(); @endphp
        @if($member->marital_status || $spouse || $familyDependants->isNotEmpty())
            <div class="tile">
                <h3 class="tile-title">Family Information</h3>
                <table class="table table-borderless table-sm">
                    @if($member->marital_status)
                        <tr><th width="180">Marital Status</th><td>{{ $member->marital_status->label() }}</td></tr>
                    @endif
                    @if($member->marital_status?->value === 'married')
                        <tr><th>Wedding Type</th><td>{{ $member->wedding_type?->label() ?? '—' }}</td></tr>
                        <tr><th>Wedding Date</th><td>{{ $member->wedding_date?->format('M d, Y') ?? '—' }}</td></tr>
                    @endif
                    @if($member->marital_status?->value === 'married' || $spouse)
                        <tr><th>Spouse Church Member</th><td>{{ ($member->spouse_church_member === 'yes' || $spouse) ? 'Yes' : 'No' }}</td></tr>
                        <tr><th>Spouse Name</th>
                            <td>
                                @if($spouse)
                                    <a href="{{ route('church.members.show', $spouse) }}">{{ $spouse->full_name }}</a>
                                    <span class="badge badge-light">Registered member</span>
                                @else
                                    {{ $member->spouse_full_name ?? '—' }}
                                @endif
                            </td>
                        </tr>
                        @if(!$spouse)
                            <tr><th>Spouse Gender</th><td>{{ $member->spouse_gender ? ucfirst($member->spouse_gender) : '—' }}</td></tr>
                            <tr><th>Spouse DOB</th><td>{{ $member->spouse_date_of_birth?->format('M d, Y') ?? '—' }}</td></tr>
                            <tr><th>Spouse Phone</th><td>{{ $member->spouse_phone_number ?? '—' }}</td></tr>
                            <tr><th>Spouse Envelope</th><td>{{ $member->spouse_envelope_number ?? '—' }}</td></tr>
                        @endif
                    @endif
                </table>

                @if($familyDependants->isNotEmpty())
                    <h5 class="mt-3">Dependants / Children</h5>
                    <table class="table table-bordered table-sm">
                        <thead><tr><th>Name</th><th>Gender</th><th>DOB</th><th>Age</th><th>Relationship</th><th>Baptism</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($familyDependants as $dependant)
                                <tr>
                                    <td>
                                        {{ $dependant->full_name }}
                                        @if($dependant->member_id !== $member->id && $dependant->member)
                                            <br><small class="text-muted">Registered under {{ $dependant->member->full_name }}</small>
                                        @endif
                                    </td>
                                    <td>{{ ucfirst($dependant->gender) }}</td>
                                    <td>{{ $dependant->date_of_birth?->format('M d, Y') ?? '—' }}</td>
                                    <td>{{ $dependant->age() ?? '—' }}</td>
                                    <td>{{ $dependant->relationship->label() }}{{ $dependant->relationship_note ? ' — '.$dependant->relationship_note : '' }}</td>
                                    <td>
                                        @if($dependant->is_baptized)
                                            Yes
                                            @if($dependant->baptism_date)
                                                <br><small class="text-muted">{{ $dependant->baptism_date->format('M d, Y') }}</small>
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        @if($dependant->linkedMember)
                                            <a href="{{ route('church.members.show', $dependant->linkedMember) }}">Independent member</a>
                                        @elseif($dependant->relationship->value === 'child' && $dependant->isEligibleForIndependence())
                                            <span class="badge badge-warning">Ready to convert</span>
                                        @else
                                            {{ $dependant->independenceStatusLabel() }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @can('viewAny', \App\Models\MemberDependant::class)
                        <a href="{{ route('church.members.children.index') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fa fa-child"></i> View All Children
                        </a>
                        @can('create', \App\Models\MemberDependant::class)
                            <a href="{{ route('church.members.children.create', ['member_id' => $member->id]) }}" class="btn btn-sm btn-primary">
                                <i class="fa fa-plus"></i> Add Child
                            </a>
                        @endcan
                    @endcan
                @elseif($member->marital_status?->value === 'married' || $spouse)
                    <p class="text-muted mt-3 mb-0">No children registered for this family yet.</p>
                    @can('create', \App\Models\MemberDependant::class)
                        <a href="{{ route('church.members.children.create', ['member_id' => $member->id]) }}" class="btn btn-sm btn-primary mt-2">
                            <i class="fa fa-plus"></i> Add Child
                        </a>
                    @endcan
                @endif
            </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="tile">
            <h3 class="tile-title">Actions</h3>
            @can('viewAny', \App\Models\Member::class)
                <a href="{{ route('church.members.index') }}" class="btn btn-secondary btn-block">
                    <i class="fa fa-arrow-left"></i> Back to Members
                </a>
            @endcan
            @can('update', $member)
                @if(! $member->isArchived())
                    <a href="{{ route('church.members.edit', $member) }}" class="btn btn-primary btn-block mt-2">
                        <i class="fa fa-pencil"></i> Edit Member
                    </a>
                @endif
            @endcan
            @can('viewAny', \App\Models\Member::class)
                @if($member->isArchived())
                    <a href="{{ route('church.members.archived') }}" class="btn btn-outline-secondary btn-block mt-2">
                        <i class="fa fa-archive"></i> Archived Members
                    </a>
                @endif
            @endcan
            @can('restore', $member)
                @if($member->isArchived())
                    <form method="POST" action="{{ route('church.members.restore', $member) }}" class="mt-2"
                        data-swal-confirm="Restore {{ $member->full_name }} to active membership?">
                        @csrf
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fa fa-undo"></i> Restore Member
                        </button>
                    </form>
                @endif
            @endcan
            @can('archive', $member)
                @if(! $member->isArchived())
                    <button type="button" class="btn btn-warning btn-block mt-2" data-toggle="modal" data-target="#archiveMemberModal-{{ $member->id }}">
                        <i class="fa fa-archive"></i> Archive Member
                    </button>
                @endif
            @endcan
            @if($spouse = $member->resolvedSpouse())
                @can('view', $spouse)
                    <a href="{{ route('church.members.show', $spouse) }}" class="btn btn-outline-primary btn-block mt-2">
                        <i class="fa fa-user"></i> View Spouse Profile
                    </a>
                @endcan
            @endif
        </div>

        @can('update', $member)
            @if($member->isTemporary() && ! $member->isArchived())
                <div class="tile">
                    <h3 class="tile-title">Temporary Membership</h3>

                    <form method="POST" action="{{ route('church.members.extend-membership', $member) }}" class="mb-4">
                        @csrf
                        <p class="text-muted small">Add more time to this member's temporary stay.</p>
                        <div class="form-group">
                            <label>Extend by</label>
                            <div class="input-group">
                                <input type="number" name="temporary_duration_value" class="form-control"
                                    min="1" max="99" value="6" required>
                                <select name="temporary_duration_unit" class="form-control" required>
                                    @foreach($durationUnits as $unit)
                                        <option value="{{ $unit->value }}">{{ $unit->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-warning btn-block">
                            <i class="fa fa-clock-o"></i> Extend Stay
                        </button>
                    </form>

                    <form method="POST" action="{{ route('church.members.convert-to-permanent', $member) }}"
                        data-swal-confirm="Convert {{ $member->full_name }} to a permanent member?">
                        @csrf
                        <p class="text-muted small">Upgrade this member to permanent membership.</p>
                        <div class="form-group">
                            <label>Member Type *</label>
                            <select name="member_type" class="form-control" required>
                                <option value="">Select</option>
                                @foreach($memberTypes as $type)
                                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fa fa-check"></i> Convert to Permanent
                        </button>
                    </form>
                </div>
            @endif
        @endcan
    </div>
</div>

@can('archive', $member)
    @if(! $member->isArchived())
        @include('church.members.partials.archive-modal', ['member' => $member])
    @endif
@endcan
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/member-wizard.css') }}">
@endpush
