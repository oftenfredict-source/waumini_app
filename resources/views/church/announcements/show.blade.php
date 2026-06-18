@extends('layouts.church')

@section('title', $announcement->title)

@section('content')
<div class="app-title">
    <div>
        <h1>
            @if($announcement->is_pinned)
                <i class="fa fa-thumb-tack text-warning"></i>
            @endif
            {{ $announcement->title }}
        </h1>
        <p>Announcement details</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.announcements.index') }}">Announcements</a></li>
        <li class="breadcrumb-item">{{ Str::limit($announcement->title, 30) }}</li>
    </ul>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="tile">
            <div class="mb-3">
                <span class="badge badge-{{ $announcement->type->badgeClass() }}">{{ $announcement->type->label() }}</span>
                <span class="badge badge-{{ $announcement->isCurrentlyActive() ? 'success' : 'secondary' }}">
                    {{ $announcement->isCurrentlyActive() ? 'Active' : 'Inactive' }}
                </span>
                @if($announcement->is_pinned)
                    <span class="badge badge-warning">Pinned</span>
                @endif
            </div>
            <div class="announcement-content" style="white-space: pre-wrap;">{{ $announcement->content }}</div>
        </div>

        @if($announcement->targetedMembers->isNotEmpty())
            <div class="tile">
                <h3 class="tile-title">Targeted Members</h3>
                <ul class="list-group list-group-flush">
                    @foreach($announcement->targetedMembers as $member)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            {{ $member->full_name }}
                            <a href="{{ route('church.members.show', $member) }}" class="btn btn-sm btn-outline-primary">View</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
    <div class="col-md-4">
        <div class="tile">
            <h3 class="tile-title">Details</h3>
            <table class="table table-borderless table-sm">
                <tr><th>Audience</th><td>{{ $announcement->targetLabel() }}</td></tr>
                <tr><th>Start Date</th><td>{{ $announcement->start_date?->format('M d, Y') ?? 'Immediately' }}</td></tr>
                <tr><th>End Date</th><td>{{ $announcement->end_date?->format('M d, Y') ?? 'No expiry' }}</td></tr>
                <tr><th>Created By</th><td>{{ $announcement->creator?->name ?? '—' }}</td></tr>
                <tr><th>Created</th><td>{{ $announcement->created_at->format('M d, Y H:i') }}</td></tr>
            </table>
            <a href="{{ route('church.announcements.index') }}" class="btn btn-secondary btn-block">
                <i class="fa fa-arrow-left"></i> Back to Announcements
            </a>
        </div>
    </div>
</div>
@endsection
