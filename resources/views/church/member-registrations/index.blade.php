@extends('layouts.church')

@section('title', 'Registration Approvals')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-user-plus"></i> Registration Approvals</h1>
        <p>Self-registration applications awaiting pastor or secretary review</p>
    </div>
    <div>
        @if($pendingCount > 0)
            <span class="badge badge-warning" style="font-size: 1rem;">{{ $pendingCount }} pending</span>
        @endif
    </div>
</div>

@include('partials.member-registration-link')

<div class="tile mb-3">
    <form method="GET" class="form-row align-items-end">
        <div class="col-md-4">
            <label class="small text-muted">Search</label>
            <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Reference, name, phone">
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
            @if($canFilterBranches && $branches->count() > 1)
                <label class="small text-muted">Branch</label>
                <select name="branch_id" class="form-control">
                    <option value="">All branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) ($filters['branch_id'] ?? '') === (string) $branch->id)>{{ $branch->displayLabel() }}</option>
                    @endforeach
                </select>
            @endif
        </div>
        <div class="col-md-2">
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
                    <th>Applicant</th>
                    <th>Phone</th>
                    <th>Branch</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($applications as $application)
                    <tr>
                        <td><code>{{ $application->application_number }}</code></td>
                        <td>{{ $application->full_name }}</td>
                        <td>{{ $application->phone_number ?? '—' }}</td>
                        <td>{{ $application->branch?->displayLabel() ?? '—' }}</td>
                        <td><span class="badge badge-{{ $application->status->badgeClass() }}">{{ $application->status->label() }}</span></td>
                        <td>{{ $application->created_at?->format('M d, Y') }}</td>
                        <td class="text-right">
                            <a href="{{ route('church.member-registrations.show', $application) }}" class="btn btn-sm btn-info">Review</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted">No registration applications found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $applications->links() }}</div>
</div>
@endsection
