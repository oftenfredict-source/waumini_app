@extends('layouts.church')

@section('title', $member->full_name)

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-user',
    'title' => $member->full_name,
    'subtitle' => __('pages.members.show_subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.members'), 'route' => 'church.members.index'],
        ['label' => $member->full_name],
    ],
])

@if($member->isArchived())
    <div class="alert alert-warning">
        <h5 class="alert-heading"><i class="fa fa-archive"></i> {{ __('pages.members.archived_member') }}</h5>
        <p class="mb-1"><strong>{{ __('pages.members.archived_label') }}</strong> {{ $member->archived_at?->format('M d, Y H:i') ?? '—' }}</p>
        <p class="mb-1"><strong>{{ __('pages.members.archived_by') }}</strong> {{ $member->archivedBy?->name ?? '—' }}</p>
        <p class="mb-0"><strong>{{ __('pages.members.reason') }}</strong> {{ $member->archive_reason ?? '—' }}</p>
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
                    <h3 class="tile-title">{{ __('members.summary.personal_information') }}</h3>
                    <table class="table table-borderless table-sm">
                        <tr><th width="180">{{ __('pages.shared.member_id') }}</th><td><code>{{ $member->member_number }}</code></td></tr>
                        <tr><th>{{ __('pages.shared.envelope_number') }}</th><td><code>{{ $member->envelope_number ?? '—' }}</code></td></tr>
                        <tr><th>{{ __('members.fields.membership_type') }}</th><td>{{ ucfirst($member->membership_type->value) }}</td></tr>
                        @if($member->isTemporary())
                            <tr><th>{{ __('pages.members.stay_duration') }}</th><td>{{ $member->temporaryDurationLabel() ?? '—' }}</td></tr>
                            <tr><th>{{ __('pages.members.expires_on') }}</th>
                                <td>
                                    {{ $member->membership_expires_at?->format('M d, Y') ?? '—' }}
                                    @if($member->membership_expires_at)
                                        @if($member->isMembershipExpired())
                                            <span class="badge badge-danger">{{ __('common.expired') }}</span>
                                        @else
                                            <span class="badge badge-info">{{ __('pages.members.days_left', ['count' => $member->membershipDaysRemaining()]) }}</span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endif
                        <tr><th>{{ __('members.fields.member_type') }}</th><td>{{ $member->member_type?->label() ?? '—' }}</td></tr>
                        <tr><th>{{ __('members.fields.gender') }}</th><td>{{ $member->gender ? ucfirst($member->gender) : '—' }}</td></tr>
                        <tr><th>{{ __('members.fields.date_of_birth') }}</th><td>{{ $member->date_of_birth?->format('M d, Y') ?? '—' }}</td></tr>
                        <tr><th>{{ __('members.fields.education_level') }}</th><td>{{ $member->education_level?->label() ?? '—' }}</td></tr>
                        <tr><th>{{ __('members.fields.profession') }}</th><td>{{ $member->profession ?? '—' }}</td></tr>
                        <tr><th>{{ __('members.fields.nida_number') }}</th><td>{{ $member->nida_number ?? '—' }}</td></tr>
                        <tr><th>{{ __('pages.members.baptism') }}</th>
                            <td>
                                @if($member->is_baptized)
                                    <span class="badge badge-info">{{ __('members.summary.baptized') }}</span>
                                    @if($member->baptism_date)
                                        <div class="small text-muted mt-1">{{ __('pages.members.baptism_date_label') }} {{ $member->baptism_date->format('M d, Y') }}</div>
                                    @endif
                                    @if($member->baptism_place)
                                        <div class="small text-muted">{{ __('pages.members.baptism_place_label') }} {{ $member->baptism_place }}</div>
                                    @endif
                                    @if($member->baptized_by)
                                        <div class="small text-muted">{{ __('pages.members.baptized_by_label') }} {{ $member->baptized_by }}</div>
                                    @endif
                                @else
                                    <span class="text-muted">{{ __('pages.members.not_baptized') }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr><th>{{ __('common.status') }}</th>
                            <td>
                                @if($member->isArchived())
                                    <span class="badge badge-secondary">{{ __('pages.members.archived_badge') }}</span>
                                @else
                                    <span class="badge badge-success">{{ __('common.active') }}</span>
                                @endif
                            </td>
                        </tr>
                        @if($member->user)
                            <tr><th>{{ __('pages.members.login_username') }}</th><td><code>{{ $member->member_number }}</code></td></tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <div class="tile">
            <h3 class="tile-title">{{ __('members.summary.contact_origin') }}</h3>
            <table class="table table-borderless table-sm">
                <tr><th width="180">{{ __('common.phone') }}</th><td>{{ $member->phone_number }}</td></tr>
                <tr><th>{{ __('members.fields.email') }}</th><td>{{ $member->email ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.members.origin') }}</th><td>{{ collect([$member->region, $member->district, $member->ward, $member->street])->filter()->implode(', ') ?: '—' }}</td></tr>
                <tr><th>{{ __('members.fields.po_box') }}</th><td>{{ $member->po_box ?? '—' }}</td></tr>
                <tr><th>{{ __('members.fields.tribe') }}</th><td>{{ $member->other_tribe ?: ($member->tribe && $member->tribe !== 'Other' ? $member->tribe : '—') }}</td></tr>
            </table>
        </div>

        <div class="tile">
            <h3 class="tile-title">{{ __('members.summary.residence') }}</h3>
            <table class="table table-borderless table-sm">
                <tr><th width="180">{{ __('common.location') }}</th><td>{{ collect([$member->residence_region, $member->residence_district, $member->residence_ward])->filter()->implode(', ') ?: '—' }}</td></tr>
                <tr><th>{{ __('pages.members.street_road') }}</th><td>{{ collect([$member->residence_street, $member->residence_road])->filter()->implode(', ') ?: '—' }}</td></tr>
                <tr><th>{{ __('members.fields.house_number') }}</th><td>{{ $member->residence_house_number ?? '—' }}</td></tr>
            </table>
        </div>

        @php $spouse = $member->resolvedSpouse(); @endphp
        @if($member->marital_status || $spouse || $familyDependants->isNotEmpty())
            <div class="tile">
                <h3 class="tile-title">{{ __('members.summary.family_information') }}</h3>
                <table class="table table-borderless table-sm">
                    @if($member->marital_status)
                        <tr><th width="180">{{ __('members.fields.marital_status') }}</th><td>{{ $member->marital_status->label() }}</td></tr>
                    @endif
                    @if($member->marital_status?->value === 'married')
                        <tr><th>{{ __('members.fields.wedding_type') }}</th><td>{{ $member->wedding_type?->label() ?? '—' }}</td></tr>
                        <tr><th>{{ __('members.fields.wedding_date') }}</th><td>{{ $member->wedding_date?->format('M d, Y') ?? '—' }}</td></tr>
                    @endif
                    @if($member->marital_status?->value === 'married' || $spouse)
                        <tr><th>{{ __('pages.members.spouse_church_member') }}</th><td>{{ ($member->spouse_church_member === 'yes' || $spouse) ? __('common.yes') : __('common.no') }}</td></tr>
                        <tr><th>{{ __('pages.members.spouse_name') }}</th>
                            <td>
                                @if($spouse)
                                    <a href="{{ route('church.members.show', $spouse) }}">{{ $spouse->full_name }}</a>
                                    <span class="badge badge-light">{{ __('pages.members.registered_member') }}</span>
                                @else
                                    {{ $member->spouse_full_name ?? '—' }}
                                @endif
                            </td>
                        </tr>
                        @if(!$spouse)
                            <tr><th>{{ __('pages.members.spouse_gender') }}</th><td>{{ $member->spouse_gender ? ucfirst($member->spouse_gender) : '—' }}</td></tr>
                            <tr><th>{{ __('pages.members.spouse_dob') }}</th><td>{{ $member->spouse_date_of_birth?->format('M d, Y') ?? '—' }}</td></tr>
                            <tr><th>{{ __('members.fields.spouse_phone') }}</th><td>{{ $member->spouse_phone_number ?? '—' }}</td></tr>
                            <tr><th>{{ __('pages.members.spouse_envelope') }}</th><td>{{ $member->spouse_envelope_number ?? '—' }}</td></tr>
                        @endif
                    @endif
                </table>

                @if($familyDependants->isNotEmpty())
                    <h5 class="mt-3">{{ __('pages.members.dependants_children') }}</h5>
                    <table class="table table-bordered table-sm">
                        <thead><tr><th>{{ __('common.name') }}</th><th>{{ __('members.fields.gender') }}</th><th>{{ __('pages.members.dob_col') }}</th><th>{{ __('pages.members_children.age_col') }}</th><th>{{ __('pages.members.relationship_col') }}</th><th>{{ __('pages.members.baptism') }}</th><th>{{ __('common.status') }}</th></tr></thead>
                        <tbody>
                            @foreach($familyDependants as $dependant)
                                <tr>
                                    <td>
                                        {{ $dependant->full_name }}
                                        @if($dependant->member_id !== $member->id && $dependant->member)
                                            <br><small class="text-muted">{{ __('pages.members.registered_under', ['name' => $dependant->member->full_name]) }}</small>
                                        @endif
                                    </td>
                                    <td>{{ ucfirst($dependant->gender) }}</td>
                                    <td>{{ $dependant->date_of_birth?->format('M d, Y') ?? '—' }}</td>
                                    <td>{{ $dependant->age() ?? '—' }}</td>
                                    <td>{{ $dependant->relationship->label() }}{{ $dependant->relationship_note ? ' — '.$dependant->relationship_note : '' }}</td>
                                    <td>
                                        @if($dependant->is_baptized)
                                            {{ __('common.yes') }}
                                            @if($dependant->baptism_date)
                                                <br><small class="text-muted">{{ $dependant->baptism_date->format('M d, Y') }}</small>
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        @if($dependant->linkedMember)
                                            <a href="{{ route('church.members.show', $dependant->linkedMember) }}">{{ __('pages.members_children.independent_member') }}</a>
                                        @elseif($dependant->relationship->value === 'child' && $dependant->isEligibleForIndependence())
                                            <span class="badge badge-warning">{{ __('pages.members_children.convert') }}</span>
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
                            <i class="fa fa-child"></i> {{ __('pages.members.view_all_children') }}
                        </a>
                        @can('create', \App\Models\MemberDependant::class)
                            <a href="{{ route('church.members.children.create', ['member_id' => $member->id]) }}" class="btn btn-sm btn-primary">
                                <i class="fa fa-plus"></i> {{ __('pages.members_children.add_child') }}
                            </a>
                        @endcan
                    @endcan
                @elseif($member->marital_status?->value === 'married' || $spouse)
                    <p class="text-muted mt-3 mb-0">{{ __('pages.members.no_children_family') }}</p>
                    @can('create', \App\Models\MemberDependant::class)
                        <a href="{{ route('church.members.children.create', ['member_id' => $member->id]) }}" class="btn btn-sm btn-primary mt-2">
                            <i class="fa fa-plus"></i> {{ __('pages.members_children.add_child') }}
                        </a>
                    @endcan
                @endif
            </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="tile">
            <h3 class="tile-title">{{ __('pages.members.actions') }}</h3>
            @can('viewAny', \App\Models\Member::class)
                <a href="{{ route('church.members.index') }}" class="btn btn-secondary btn-block">
                    <i class="fa fa-arrow-left"></i> {{ __('pages.members.back_to_members') }}
                </a>
            @endcan
            @can('update', $member)
                @if(! $member->isArchived())
                    <a href="{{ route('church.members.edit', $member) }}" class="btn btn-primary btn-block mt-2">
                        <i class="fa fa-pencil"></i> {{ __('pages.members.edit_member') }}
                    </a>
                @endif
            @endcan
            @can('viewAny', \App\Models\Member::class)
                @if($member->isArchived())
                    <a href="{{ route('church.members.archived') }}" class="btn btn-outline-secondary btn-block mt-2">
                        <i class="fa fa-archive"></i> {{ __('pages.members.archived_members') }}
                    </a>
                @endif
            @endcan
            @can('restore', $member)
                @if($member->isArchived())
                    <form method="POST" action="{{ route('church.members.restore', $member) }}" class="mt-2"
                        data-swal-confirm="{{ __('pages.members.restore_confirm', ['name' => $member->full_name]) }}">
                        @csrf
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fa fa-undo"></i> {{ __('pages.members.restore_member') }}
                        </button>
                    </form>
                @endif
            @endcan
            @can('archive', $member)
                @if(! $member->isArchived())
                    <button type="button" class="btn btn-warning btn-block mt-2" data-toggle="modal" data-target="#archiveMemberModal-{{ $member->id }}">
                        <i class="fa fa-archive"></i> {{ __('pages.members.archive_member') }}
                    </button>
                @endif
            @endcan
            @if($spouse = $member->resolvedSpouse())
                @can('view', $spouse)
                    <a href="{{ route('church.members.show', $spouse) }}" class="btn btn-outline-primary btn-block mt-2">
                        <i class="fa fa-user"></i> {{ __('pages.members.view_spouse_profile') }}
                    </a>
                @endcan
            @endif
        </div>

        @can('update', $member)
            @if($member->isTemporary() && ! $member->isArchived())
                <div class="tile">
                    <h3 class="tile-title">{{ __('pages.members.temporary_membership') }}</h3>

                    <form method="POST" action="{{ route('church.members.extend-membership', $member) }}" class="mb-4">
                        @csrf
                        <p class="text-muted small">{{ __('pages.members.extend_stay_hint') }}</p>
                        <div class="form-group">
                            <label>{{ __('pages.members.extend_by') }}</label>
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
                            <i class="fa fa-clock-o"></i> {{ __('pages.members.extend_stay') }}
                        </button>
                    </form>

                    <form method="POST" action="{{ route('church.members.convert-to-permanent', $member) }}"
                        data-swal-confirm="{{ __('pages.members.convert_confirm', ['name' => $member->full_name]) }}">
                        @csrf
                        <p class="text-muted small">{{ __('pages.members.convert_permanent_hint') }}</p>
                        <div class="form-group">
                            <label>{{ __('members.fields.member_type') }} *</label>
                            <select name="member_type" class="form-control" required>
                                <option value="">{{ __('pages.shared.select') }}</option>
                                @foreach($memberTypes as $type)
                                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fa fa-check"></i> {{ __('pages.members.convert_to_permanent') }}
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
