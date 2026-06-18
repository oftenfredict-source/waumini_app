@extends('layouts.church')

@section('title', 'Archived Members')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-archive"></i> Archived Members</h1>
        <p>Members removed from the active list with recorded reasons</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.members.index') }}">Members</a></li>
        <li class="breadcrumb-item">Archived</li>
    </ul>
</div>

<div class="row mb-3">
    <div class="col-md-8">
        <form method="GET" class="form-inline">
            <input type="text" name="search" class="form-control mr-2 mb-2" placeholder="Search name, number, phone..."
                value="{{ $filters['search'] ?? '' }}">
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
        <a href="{{ route('church.members.index') }}" class="btn btn-outline-primary mb-2">
            <i class="fa fa-users"></i> Active Members
        </a>
    </div>
</div>

<div class="tile">
    <div class="tile-body">
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>Member ID</th>
                        <th>Full Name</th>
                        @if($branchesEnabled ?? false)
                            <th>Branch</th>
                        @endif
                        <th>Phone</th>
                        <th>Archived On</th>
                        <th>Archived By</th>
                        <th>Reason</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $member)
                        <tr>
                            <td><code>{{ $member->member_number }}</code></td>
                            <td>{{ $member->full_name }}</td>
                            @if($branchesEnabled ?? false)
                                <td>{{ $member->branch?->name ?? '—' }}</td>
                            @endif
                            <td>{{ $member->phone_number ?? '—' }}</td>
                            <td>{{ $member->archived_at?->format('M d, Y H:i') ?? '—' }}</td>
                            <td>{{ $member->archivedBy?->name ?? '—' }}</td>
                            <td>
                                <span title="{{ $member->archive_reason }}">
                                    {{ \Illuminate\Support\Str::limit($member->archive_reason ?? '—', 60) }}
                                </span>
                            </td>
                            <td class="text-nowrap">
                                <div class="btn-group btn-group-sm" role="group">
                                    @can('view', $member)
                                        <a href="{{ route('church.members.show', $member) }}" class="btn btn-info" title="View profile">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    @endcan
                                    @can('restore', $member)
                                        <form method="POST" action="{{ route('church.members.restore', $member) }}" class="d-inline"
                                            data-swal-confirm="Restore {{ $member->full_name }} to active membership? They will be able to log in again.">
                                            @csrf
                                            <button type="submit" class="btn btn-success" title="Restore member">
                                                <i class="fa fa-undo"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ ($branchesEnabled ?? false) ? 8 : 7 }}" class="text-center text-muted py-4">
                                No archived members found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $members->links() }}
    </div>
</div>
@endsection
