@extends('layouts.church')

@section('title', 'Members')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-users"></i> Members</h1>
        <p>View and manage church members</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">Members</li>
    </ul>
</div>

<div class="row mb-3">
    <div class="col-md-8">
        <form method="GET" class="form-inline">
            <input type="text" name="search" class="form-control mr-2 mb-2" placeholder="Search name, number, phone..."
                value="{{ $filters['search'] ?? '' }}">
            <select name="membership_type" class="form-control mr-2 mb-2">
                <option value="">All membership types</option>
                <option value="permanent" @selected(($filters['membership_type'] ?? '') === 'permanent')>Permanent</option>
                <option value="temporary" @selected(($filters['membership_type'] ?? '') === 'temporary')>Temporary</option>
            </select>
            @if($canFilterBranches && $branches->count() > 1)
                <select name="branch_id" class="form-control mr-2 mb-2">
                    <option value="">All branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) ($filters['branch_id'] ?? '') === (string) $branch->id)>{{ $branch->displayLabel() }}</option>
                    @endforeach
                </select>
            @endif
            <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-search"></i> Search</button>
        </form>
    </div>
    <div class="col-md-4 text-md-right">
        <a href="{{ route('church.members.archived') }}" class="btn btn-outline-secondary mb-2 mr-1">
            <i class="fa fa-archive"></i> Archived Members
        </a>
        @can('create', \App\Models\Member::class)
            <a href="{{ route('church.members.create') }}" class="btn btn-primary mb-2">
                <i class="fa fa-user-plus"></i> Register Member
            </a>
        @endcan
    </div>
</div>

<div class="tile">
    <div class="tile-body">
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>Envelope #</th>
                        <th>Member ID</th>
                        <th>Full Name</th>
                        @if($branchesEnabled ?? false)
                            <th>Branch</th>
                        @endif
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th width="200">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $member)
                        <tr>
                            <td><code>{{ $member->envelope_number ?? '—' }}</code></td>
                            <td><code>{{ $member->member_number }}</code></td>
                            <td>
                                {{ $member->full_name }}
                                @if($member->spouseMember)
                                    <br><small class="text-muted">Spouse:
                                        <a href="{{ route('church.members.show', $member->spouseMember) }}">{{ $member->spouseMember->full_name }}</a>
                                    </small>
                                @endif
                            </td>
                            @if($branchesEnabled ?? false)
                                <td>{{ $member->branch?->name ?? '—' }}</td>
                            @endif
                            <td>{{ $member->phone_number }}</td>
                            <td>{{ $member->email ?? '—' }}</td>
                            <td>{{ ucfirst($member->membership_type->value) }}
                                @if($member->membership_type->value === 'temporary' && $member->membership_expires_at)
                                    <br><small class="text-muted">
                                        Expires {{ $member->membership_expires_at->format('M d, Y') }}
                                        @if($member->isMembershipExpired())
                                            <span class="text-danger">(expired)</span>
                                        @endif
                                    </small>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-success">Active</span>
                            </td>
                            <td>{{ $member->created_at->format('M d, Y') }}</td>
                            <td class="text-nowrap">
                                <div class="btn-group btn-group-sm" role="group">
                                    @can('view', $member)
                                        <a href="{{ route('church.members.show', $member) }}" class="btn btn-info" title="View profile">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    @endcan
                                    @can('update', $member)
                                        <a href="{{ route('church.members.edit', $member) }}" class="btn btn-primary" title="Edit member">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('resetPassword', $member)
                                        @if($member->user)
                                            <form method="POST" action="{{ route('church.members.reset-password', $member) }}" class="d-inline"
                                                data-swal-confirm="Reset password for {{ $member->full_name }}? The password will be set to their last name in CAPITAL letters.">
                                                @csrf
                                                <button type="submit" class="btn btn-warning" title="Reset password">
                                                    <i class="fa fa-key"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                    @can('archive', $member)
                                        @if(! $member->isArchived())
                                            <button type="button" class="btn btn-secondary" title="Archive member"
                                                    data-toggle="modal" data-target="#archiveMemberModal-{{ $member->id }}">
                                                <i class="fa fa-archive"></i>
                                            </button>
                                        @endif
                                    @endcan
                                    @can('delete', $member)
                                        <form method="POST" action="{{ route('church.members.destroy', $member) }}" class="d-inline"
                                            data-swal-confirm="Permanently delete {{ $member->full_name }}? This cannot be undone."
                                            data-swal-delete
                                            data-swal-confirm-text="Yes, delete">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" title="Delete member">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ ($branchesEnabled ?? false) ? 10 : 9 }}" class="text-center text-muted py-4">
                                No members found.
                                @can('create', \App\Models\Member::class)
                                    <a href="{{ route('church.members.create') }}">Register the first member</a>.
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $members->links() }}
    </div>
</div>

@foreach($members as $member)
    @can('archive', $member)
        @if(! $member->isArchived())
            @include('church.members.partials.archive-modal', ['member' => $member])
        @endif
    @endcan
@endforeach

@if($errors->has('archive_reason'))
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var modal = document.querySelector('.modal');
                if (modal) {
                    $(modal).modal('show');
                }
            });
        </script>
    @endpush
@endif
@endsection
