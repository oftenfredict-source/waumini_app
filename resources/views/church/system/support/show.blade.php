@extends('layouts.church')

@section('title', $ticket->subject)

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-life-ring',
    'title' => __('pages.support.details'),
    'subtitle' => $ticket->subject,
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.support_tickets'), 'route' => 'church.system.support.index'],
        ['label' => __('pages.support.details')],
    ],
])

<div class="mb-3">
    <a href="{{ route('church.system.support.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-arrow-left"></i> {{ __('pages.support.back_to_list') }}
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="tile">
            <h4 class="mb-3">{{ $ticket->subject }}</h4>
            <div class="mb-3">
                <span class="badge badge-secondary mr-1">{{ __('pages.support.categories.'.$ticket->category) }}</span>
                <span class="badge badge-info mr-1">{{ __('pages.support.priorities.'.$ticket->priority) }}</span>
                <span class="badge badge-{{ in_array($ticket->status, ['resolved','closed'], true) ? 'success' : 'warning' }}">
                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                </span>
            </div>
            <div class="border rounded p-3 bg-light" style="white-space: pre-wrap;">{{ $ticket->body }}</div>
            <p class="text-muted small mt-3 mb-0">{{ __('pages.support.waiting_response') }}</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="tile">
            <h5 class="mb-3">{{ __('pages.support.details') }}</h5>
            <dl class="mb-0">
                <dt>{{ __('pages.support.status') }}</dt>
                <dd>{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</dd>
                <dt>{{ __('pages.support.submitted_by') }}</dt>
                <dd>{{ $ticket->creator?->name ?? '—' }}</dd>
                <dt>{{ __('pages.support.assigned') }}</dt>
                <dd>{{ $ticket->assignee?->name ?? '—' }}</dd>
                <dt>{{ __('common.created') }}</dt>
                <dd>{{ $ticket->created_at->format('M d, Y H:i') }}</dd>
                @if($ticket->closed_at)
                    <dt>{{ __('pages.support.resolved') }}</dt>
                    <dd>{{ $ticket->closed_at->format('M d, Y H:i') }}</dd>
                @endif
            </dl>
        </div>
    </div>
</div>
@endsection
