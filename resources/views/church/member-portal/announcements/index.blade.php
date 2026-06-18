@extends('layouts.church')

@section('title', 'Announcements')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-bullhorn"></i> Announcements</h1>
        <p>Messages shared with you by {{ $church->name }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.member.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">Announcements</li>
    </ul>
</div>

<div class="tile">
    @forelse($announcements as $announcement)
        <div class="p-3 {{ ! $loop->last ? 'border-bottom' : '' }}">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <h4 class="mb-1">
                    @if($announcement->is_pinned)<i class="fa fa-thumb-tack text-warning"></i> @endif
                    <a href="{{ route('church.member.announcements.show', $announcement) }}">{{ $announcement->title }}</a>
                </h4>
                <small class="text-muted">{{ $announcement->created_at?->format('M d, Y') }}</small>
            </div>
            <div class="mb-2">
                <span class="badge badge-light">{{ $announcement->type?->label() ?? 'General' }}</span>
            </div>
            <p class="text-muted mb-0">{{ Str::limit(strip_tags($announcement->content), 180) }}</p>
        </div>
    @empty
        <p class="p-4 text-muted mb-0">No announcements are available for you at this time.</p>
    @endforelse
</div>
@endsection
