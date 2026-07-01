@extends('layouts.church')

@section('title', __('pages.member_portal_announcements.title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-bullhorn',
    'title' => __('pages.member_portal_announcements.title'),
    'subtitle' => __('pages.member_portal_announcements.subtitle', ['church' => $church->name]),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.member.dashboard'],
        ['label' => __('menu.announcements')],
    ],
])

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
                <span class="badge badge-light">{{ $announcement->type?->label() ?? __('common.general') }}</span>
            </div>
            <p class="text-muted mb-0">{{ Str::limit(strip_tags($announcement->content), 180) }}</p>
        </div>
    @empty
        <p class="p-4 text-muted mb-0">{{ __('pages.member_portal_announcements.empty') }}</p>
    @endforelse
</div>
@endsection
