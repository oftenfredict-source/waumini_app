@extends('layouts.church')

@section('title', 'My Profile')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/member-wizard.css') }}">
<style>
    .profile-preview-lg {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #eee;
    }
</style>
@endpush

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-user"></i> My Profile</h1>
        <p>View your information and update contact details</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ auth()->user()->isChurchMember() ? route('church.member.dashboard') : route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">My Profile</li>
    </ul>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="tile mb-3">
            <h3 class="tile-title">Personal Information</h3>
            <div class="d-flex align-items-start mb-3">
                @if($member->profilePictureUrl())
                    <img src="{{ $member->profilePictureUrl() }}" alt="{{ $member->full_name }}" class="profile-preview-lg mr-3">
                @endif
                <table class="table table-borderless table-sm mb-0">
                    <tr><th width="160">Full Name</th><td>{{ $member->full_name }}</td></tr>
                    <tr><th>Member ID</th><td><code>{{ $member->member_number }}</code></td></tr>
                    <tr><th>Envelope</th><td><code>{{ $member->envelope_number ?? '—' }}</code></td></tr>
                    <tr><th>Membership</th><td>{{ ucfirst($member->membership_type->value) }}</td></tr>
                    <tr><th>Member Type</th><td>{{ $member->member_type?->label() ?? '—' }}</td></tr>
                    <tr><th>Gender</th><td>{{ $member->gender ? ucfirst($member->gender) : '—' }}</td></tr>
                    <tr><th>Date of Birth</th><td>{{ $member->date_of_birth?->format('M d, Y') ?? '—' }}</td></tr>
                    <tr><th>Email</th><td>{{ $member->email ?? '—' }}</td></tr>
                    <tr><th>Status</th><td>{{ ucfirst($member->status->value) }}</td></tr>
                    <tr><th>Joined</th><td>{{ $member->membership_date?->format('M d, Y') ?? '—' }}</td></tr>
                </table>
            </div>
        </div>

        <div class="tile mb-3">
            <h3 class="tile-title">Contact & Residence</h3>
            <table class="table table-borderless table-sm">
                <tr><th width="160">Phone</th><td>{{ $member->phone_number ?? '—' }}</td></tr>
                <tr><th>Origin</th><td>{{ collect([$member->region, $member->district, $member->ward])->filter()->implode(', ') ?: '—' }}</td></tr>
                <tr><th>Residence</th><td>{{ collect([$member->residence_region, $member->residence_district, $member->residence_ward])->filter()->implode(', ') ?: '—' }}</td></tr>
            </table>
        </div>

        @if($member->departments->isNotEmpty())
            <div class="tile mb-3">
                <h3 class="tile-title">Departments</h3>
                <ul class="mb-0">
                    @foreach($member->departments as $department)
                        <li>{{ $department->name }}@if($department->pivot?->role) — {{ $department->pivot->role }}@endif</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($familyDependants->isNotEmpty())
            <div class="tile mb-3">
                <h3 class="tile-title">Family / Dependants</h3>
                <table class="table table-sm table-bordered">
                    <thead><tr><th>Name</th><th>Relationship</th><th>DOB</th></tr></thead>
                    <tbody>
                        @foreach($familyDependants as $dependant)
                            <tr>
                                <td>{{ $dependant->full_name }}</td>
                                <td>{{ $dependant->relationship->label() }}</td>
                                <td>{{ $dependant->date_of_birth?->format('M d, Y') ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="tile mb-3">
            <h3 class="tile-title">Update Contact & Photo</h3>
            <form method="POST" action="{{ route('church.member.profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone_number" class="form-control @error('phone_number') is-invalid @enderror"
                           value="{{ old('phone_number', $member->phone_number) }}" placeholder="+255 ...">
                    @error('phone_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>Profile Picture</label>
                    <input type="file" name="profile_picture" class="form-control-file @error('profile_picture') is-invalid @enderror" accept="image/*">
                    <small class="text-muted">Max 2MB. JPG, PNG, or GIF.</small>
                    @error('profile_picture')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fa fa-save"></i> Save Changes
                </button>
            </form>
        </div>

        <div class="tile">
            <h3 class="tile-title">Change Password</h3>
            <form method="POST" action="{{ route('church.member.profile.password') }}">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                    @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-warning btn-block">
                    <i class="fa fa-key"></i> Update Password
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
