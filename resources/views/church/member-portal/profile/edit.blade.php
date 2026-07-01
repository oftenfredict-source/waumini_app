@extends('layouts.church')

@section('title', __('pages.member_portal_profile.title'))

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
@include('partials.page-header', [
    'icon' => 'fa fa-user',
    'title' => __('pages.member_portal_profile.title'),
    'subtitle' => __('pages.member_portal_profile.subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => auth()->user()->isChurchMember() ? 'church.member.dashboard' : 'church.dashboard'],
        ['label' => __('menu.my_profile')],
    ],
])

<div class="row">
    <div class="col-lg-8">
        <div class="tile mb-3">
            <h3 class="tile-title">{{ __('members.summary.personal_information') }}</h3>
            <div class="d-flex align-items-start mb-3">
                @if($member->profilePictureUrl())
                    <img src="{{ $member->profilePictureUrl() }}" alt="{{ $member->full_name }}" class="profile-preview-lg mr-3">
                @endif
                <table class="table table-borderless table-sm mb-0">
                    <tr><th width="160">{{ __('members.fields.full_name') }}</th><td>{{ $member->full_name }}</td></tr>
                    <tr><th>{{ __('pages.shared.member_id') }}</th><td><code>{{ $member->member_number }}</code></td></tr>
                    <tr><th>{{ __('pages.shared.envelope') }}</th><td><code>{{ $member->envelope_number ?? '—' }}</code></td></tr>
                    <tr><th>{{ __('pages.member_portal_profile.membership') }}</th><td>{{ ucfirst($member->membership_type->value) }}</td></tr>
                    <tr><th>{{ __('members.fields.member_type') }}</th><td>{{ $member->member_type?->label() ?? '—' }}</td></tr>
                    <tr><th>{{ __('members.fields.gender') }}</th><td>{{ $member->gender ? ucfirst($member->gender) : '—' }}</td></tr>
                    <tr><th>{{ __('members.fields.date_of_birth') }}</th><td>{{ $member->date_of_birth?->format('M d, Y') ?? '—' }}</td></tr>
                    <tr><th>{{ __('members.fields.email') }}</th><td>{{ $member->email ?? '—' }}</td></tr>
                    <tr><th>{{ __('common.status') }}</th><td>{{ ucfirst($member->status->value) }}</td></tr>
                    <tr><th>{{ __('pages.member_portal_profile.joined') }}</th><td>{{ $member->membership_date?->format('M d, Y') ?? '—' }}</td></tr>
                </table>
            </div>
        </div>

        <div class="tile mb-3">
            <h3 class="tile-title">{{ __('pages.member_portal_profile.contact_residence') }}</h3>
            <table class="table table-borderless table-sm">
                <tr><th width="160">{{ __('common.phone') }}</th><td>{{ $member->phone_number ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.members.origin') }}</th><td>{{ collect([$member->region, $member->district, $member->ward])->filter()->implode(', ') ?: '—' }}</td></tr>
                <tr><th>{{ __('pages.member_portal_profile.residence') }}</th><td>{{ collect([$member->residence_region, $member->residence_district, $member->residence_ward])->filter()->implode(', ') ?: '—' }}</td></tr>
            </table>
        </div>

        @if($member->departments->isNotEmpty())
            <div class="tile mb-3">
                <h3 class="tile-title">{{ __('pages.member_portal_profile.departments') }}</h3>
                <ul class="mb-0">
                    @foreach($member->departments as $department)
                        <li>{{ $department->name }}@if($department->pivot?->role) — {{ $department->pivot->role }}@endif</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($familyDependants->isNotEmpty())
            <div class="tile mb-3">
                <h3 class="tile-title">{{ __('pages.member_portal_profile.family_dependants') }}</h3>
                <table class="table table-sm table-bordered">
                    <thead><tr><th>{{ __('common.name') }}</th><th>{{ __('members.fields.relationship') }}</th><th>{{ __('pages.members.dob_col') }}</th></tr></thead>
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
            <h3 class="tile-title">{{ __('pages.member_portal_profile.update_contact_photo') }}</h3>
            <form method="POST" action="{{ route('church.member.profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>{{ __('pages.member_portal_profile.phone_number') }}</label>
                    <input type="text" name="phone_number" class="form-control @error('phone_number') is-invalid @enderror"
                           value="{{ old('phone_number', $member->phone_number) }}" placeholder="{{ __('pages.member_portal_profile.phone_placeholder') }}">
                    @error('phone_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>{{ __('pages.member_portal_profile.profile_picture') }}</label>
                    <input type="file" name="profile_picture" class="form-control-file @error('profile_picture') is-invalid @enderror" accept="image/*">
                    <small class="text-muted">{{ __('pages.member_portal_profile.profile_picture_hint') }}</small>
                    @error('profile_picture')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fa fa-save"></i> {{ __('pages.shared.save_changes') }}
                </button>
            </form>
        </div>

        <div class="tile">
            <h3 class="tile-title">{{ __('pages.member_portal_profile.change_password') }}</h3>
            <form method="POST" action="{{ route('church.member.profile.password') }}">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>{{ __('pages.member_portal_profile.current_password') }}</label>
                    <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                    @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>{{ __('pages.member_portal_profile.new_password') }}</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>{{ __('pages.member_portal_profile.confirm_new_password') }}</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-warning btn-block">
                    <i class="fa fa-key"></i> {{ __('pages.member_portal_profile.update_password') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
