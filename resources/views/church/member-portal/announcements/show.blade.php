@extends('layouts.church')

@section('title', $announcement->title)

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-bullhorn',
    'title' => $announcement->title,
    'subtitle' => $announcement->created_at?->format('l, F j, Y'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.member.dashboard'],
        ['label' => __('pages.member_portal_announcements.title'), 'route' => 'church.member.announcements.index'],
        ['label' => Str::limit($announcement->title, 30)],
    ],
])

<div class="tile">
    <div class="mb-3">
        <span class="badge badge-primary">{{ $announcement->type?->label() ?? __('common.general') }}</span>
        @if($announcement->is_pinned)<span class="badge badge-warning">{{ __('pages.shared.pinned') }}</span>@endif
    </div>
    <div class="announcement-content">
        {!! nl2br(e($announcement->content)) !!}
    </div>
    @if($announcement->creator)
        <hr>
        <small class="text-muted">{{ __('pages.member_portal_announcements.posted_by', ['name' => $announcement->creator->name]) }}</small>
    @endif
</div>

<a href="{{ route('church.member.announcements.index') }}" class="btn btn-secondary">
    <i class="fa fa-arrow-left"></i> {{ __('pages.member_portal_announcements.back_to_announcements') }}
</a>
@endsection
