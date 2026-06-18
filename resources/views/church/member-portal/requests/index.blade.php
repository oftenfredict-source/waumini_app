@extends('layouts.church')

@section('title', 'My Requests')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-envelope"></i> My Requests</h1>
        <p>Submit and track certificates, issues, and other requests</p>
    </div>
    <div class="text-right">
        <a href="{{ route('church.member.requests.create') }}" class="btn btn-primary">
            <i class="fa fa-plus"></i> New Request
        </a>
    </div>
</div>

<div class="tile">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Type</th>
                    <th>Subject</th>
                    <th>Assigned To</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $item)
                    <tr>
                        <td><code>{{ $item->reference_number }}</code></td>
                        <td>{{ $item->type->label() }}</td>
                        <td>{{ Str::limit($item->subject, 40) }}</td>
                        <td>{{ $item->assignedLeader?->member?->full_name ?? '—' }}<br><small class="text-muted">{{ $item->assignedLeader?->positionLabel() }}</small></td>
                        <td><span class="badge badge-{{ $item->status->badgeClass() }}">{{ $item->status->label() }}</span></td>
                        <td>{{ $item->created_at?->format('M d, Y') }}</td>
                        <td class="text-right text-nowrap">
                            <a href="{{ route('church.member.requests.show', $item) }}" class="btn btn-sm btn-info">View</a>
                            @if($item->hasDownloadableCertificate())
                                <a href="{{ route('church.member.requests.certificate', $item) }}" class="btn btn-sm btn-success" title="Download certificate">
                                    <i class="fa fa-download"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted">You have not submitted any requests yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($requests->hasPages())
        <div class="mt-3">{{ $requests->links() }}</div>
    @endif
</div>
@endsection
