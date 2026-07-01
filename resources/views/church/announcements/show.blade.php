@extends('layouts.church')

@section('title', $announcement->title)

@section('content')
@include('partials.page-header', [
    'icon' => $announcement->is_pinned ? 'fa fa-thumb-tack text-warning' : 'fa fa-bullhorn',
    'title' => $announcement->title,
    'subtitle' => __('pages.shared.announcement_details'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.announcements'), 'route' => 'church.announcements.index'],
        ['label' => Str::limit($announcement->title, 30)],
    ],
])

<div class="row">
    <div class="col-md-8">
        <div class="tile">
            <div class="mb-3">
                <span class="badge badge-{{ $announcement->type->badgeClass() }}">{{ $announcement->type->label() }}</span>
                <span class="badge badge-{{ $announcement->isCurrentlyActive() ? 'success' : 'secondary' }}">
                    {{ $announcement->isCurrentlyActive() ? __('common.active') : __('common.inactive') }}
                </span>
                @if($announcement->is_pinned)
                    <span class="badge badge-warning">{{ __('pages.shared.pinned') }}</span>
                @endif
            </div>
            <div class="announcement-content" style="white-space: pre-wrap;">{{ $announcement->content }}</div>
        </div>

        @if($announcement->targetedMembers->isNotEmpty())
            <div class="tile">
                <h3 class="tile-title">{{ __('pages.shared.targeted_members') }}</h3>
                <ul class="list-group list-group-flush">
                    @foreach($announcement->targetedMembers as $member)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            {{ $member->full_name }}
                            <a href="{{ route('church.members.show', $member) }}" class="btn btn-sm btn-outline-primary">{{ __('common.view') }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
    <div class="col-md-4">
        <div class="tile">
            <h3 class="tile-title">{{ __('common.details') }}</h3>
            <table class="table table-borderless table-sm">
                <tr><th>{{ __('pages.shared.audience') }}</th><td>{{ $announcement->targetLabel() }}</td></tr>
                <tr><th>{{ __('pages.shared.start_date') }}</th><td>{{ $announcement->start_date?->format('M d, Y') ?? __('pages.shared.immediately') }}</td></tr>
                <tr><th>{{ __('pages.shared.end_date') }}</th><td>{{ $announcement->end_date?->format('M d, Y') ?? __('pages.shared.no_expiry') }}</td></tr>
                <tr><th>{{ __('pages.shared.created_by') }}</th><td>{{ $announcement->creator?->name ?? '—' }}</td></tr>
                <tr><th>{{ __('common.created') }}</th><td>{{ $announcement->created_at->format('M d, Y H:i') }}</td></tr>
            </table>
            <a href="{{ route('church.announcements.index') }}" class="btn btn-secondary btn-block">
                <i class="fa fa-arrow-left"></i> {{ __('pages.shared.back_to', ['module' => __('pages.announcements.title')]) }}
            </a>
        </div>
    </div>
</div>
@endsection
