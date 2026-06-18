@extends('layouts.church')

@section('title', 'Member Requests')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-envelope-open"></i> Member Requests</h1>
        <p>Requests submitted by church members</p>
    </div>
</div>

<div class="tile mb-3">
    <form method="GET" class="form-row align-items-end">
        <div class="col-md-4">
            <label class="small text-muted">Search</label>
            <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Reference, subject, member name">
        </div>
        <div class="col-md-3">
            <label class="small text-muted">Status</label>
            <select name="status" class="form-control">
                <option value="">All statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="small text-muted">Filter</label>
            <select name="filter" class="form-control">
                <option value="">All requests</option>
                <option value="mine" @selected(($filters['filter'] ?? '') === 'mine')>Assigned to me</option>
            </select>
        </div>
        <div class="col-md-2">
            @if($canFilterBranches && $branches->count() > 1)
                <label class="small text-muted">Branch</label>
                <select name="branch_id" class="form-control mb-2">
                    <option value="">All branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) ($filters['branch_id'] ?? '') === (string) $branch->id)>{{ $branch->displayLabel() }}</option>
                    @endforeach
                </select>
            @endif
            <button type="submit" class="btn btn-primary btn-block">Filter</button>
        </div>
    </form>
</div>

<div class="tile">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Member</th>
                    <th>Type</th>
                    <th>Subject</th>
                    <th>Assigned Leader</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $item)
                    <tr>
                        <td><code>{{ $item->reference_number }}</code></td>
                        <td>{{ $item->member?->full_name }}</td>
                        <td>{{ $item->type->label() }}</td>
                        <td>{{ Str::limit($item->subject, 35) }}</td>
                        <td>{{ $item->assignedLeader?->member?->full_name ?? '—' }}<br><small class="text-muted">{{ $item->assignedLeader?->positionLabel() }}</small></td>
                        <td><span class="badge badge-{{ $item->status->badgeClass() }}">{{ $item->status->label() }}</span></td>
                        <td>{{ $item->created_at?->format('M d, Y') }}</td>
                        <td class="text-right">
                            <a href="{{ route('church.member-requests.show', $item) }}" class="btn btn-sm btn-info">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-muted">No member requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($requests->hasPages())
        <div class="mt-3">{{ $requests->links() }}</div>
    @endif
</div>
@endsection
