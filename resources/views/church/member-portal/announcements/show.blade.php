@extends('layouts.church')

@section('title', $announcement->title)

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-bullhorn"></i> {{ $announcement->title }}</h1>
        <p>{{ $announcement->created_at?->format('l, F j, Y') }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.member.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.member.announcements.index') }}">Announcements</a></li>
        <li class="breadcrumb-item">{{ Str::limit($announcement->title, 30) }}</li>
    </ul>
</div>

<div class="tile">
    <div class="mb-3">
        <span class="badge badge-primary">{{ $announcement->type?->label() ?? 'General' }}</span>
        @if($announcement->is_pinned)<span class="badge badge-warning">Pinned</span>@endif
    </div>
    <div class="announcement-content">
        {!! nl2br(e($announcement->content)) !!}
    </div>
    @if($announcement->creator)
        <hr>
        <small class="text-muted">Posted by {{ $announcement->creator->name }}</small>
    @endif
</div>

<a href="{{ route('church.member.announcements.index') }}" class="btn btn-secondary">
    <i class="fa fa-arrow-left"></i> Back to Announcements
</a>
@endsection
