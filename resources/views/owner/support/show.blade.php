@extends('layouts.owner')

@section('title', $ticket->subject)

@section('content')
@php
    $priorityStyles = [
        'low' => 'background:rgba(82,82,91,.1);color:#52525b;',
        'medium' => 'background:rgba(8,145,178,.12);color:#0891b2;',
        'high' => 'background:rgba(217,119,6,.12);color:#d97706;',
        'urgent' => 'background:rgba(220,38,38,.12);color:#dc2626;',
    ];
    $statusStyles = [
        'open' => 'background:rgba(217,119,6,.12);color:#d97706;',
        'in_progress' => 'background:rgba(8,145,178,.12);color:#0891b2;',
        'waiting' => 'background:rgba(148,0,0,.1);color:#940000;',
        'resolved' => 'background:rgba(22,163,74,.12);color:#16a34a;',
        'closed' => 'background:rgba(82,82,91,.1);color:#52525b;',
    ];
    $priorityStyle = $priorityStyles[$ticket->priority] ?? $priorityStyles['medium'];
    $statusStyle = $statusStyles[$ticket->status] ?? $statusStyles['open'];
@endphp

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">{{ $ticket->subject }}</h1>
        <p class="text-muted mb-0">{{ $ticket->church?->name ?? '—' }}</p>
    </div>
    <a href="{{ route('owner.support.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> {{ __('owner.sup.back_to_list') }}
    </a>
</div>

<div class="row g-3 g-lg-4">
    <div class="col-lg-8">
        <div class="card owner-panel-card mb-4">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <span class="badge rounded-pill text-bg-light border">
                    {{ ucfirst(str_replace('_', ' ', $ticket->category)) }}
                </span>
                <span class="badge rounded-pill" style="{{ $priorityStyle }}">
                    {{ ucfirst($ticket->priority) }}
                </span>
                <span class="badge rounded-pill" style="{{ $statusStyle }}">
                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                </span>
            </div>
            <div class="card-body">
                <h2 class="h6 text-muted mb-3">{{ __('owner.sup.message') }}</h2>
                <div class="rounded-3 p-3 mb-0" style="background:rgba(148,0,0,.04);white-space:pre-wrap;">{{ $ticket->body }}</div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card owner-panel-card mb-4">
            <div class="card-header">{{ __('owner.sup.update_status') }}</div>
            <div class="card-body">
                <form method="POST" action="{{ route('owner.support.update', $ticket) }}" class="d-grid gap-3">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="form-label" for="status">{{ __('owner.status') }}</label>
                        <select id="status" name="status" class="form-select" required>
                            @foreach(['open','in_progress','waiting','resolved','closed'] as $status)
                                <option value="{{ $status }}" @selected(old('status', $ticket->status) === $status)>
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="priority">{{ __('owner.sup.priority') }}</label>
                        <select id="priority" name="priority" class="form-select">
                            @foreach(['low','medium','high','urgent'] as $priority)
                                <option value="{{ $priority }}" @selected(old('priority', $ticket->priority) === $priority)>
                                    {{ ucfirst($priority) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> {{ __('common.save') }}
                    </button>
                </form>
            </div>
        </div>

        <div class="card owner-panel-card">
            <div class="card-header">{{ __('owner.sup.heading') }}</div>
            <div class="card-body">
                <dl class="row mb-0 gy-2">
                    <dt class="col-5 text-muted">{{ __('owner.church_label') }}</dt>
                    <dd class="col-7 mb-0">{{ $ticket->church?->name ?? '—' }}</dd>

                    <dt class="col-5 text-muted">{{ __('owner.sup.submitted_by') }}</dt>
                    <dd class="col-7 mb-0">
                        {{ $ticket->creator?->name ?? '—' }}
                        @if($ticket->creator?->email)
                            <div class="small text-muted">{{ $ticket->creator->email }}</div>
                        @endif
                    </dd>

                    <dt class="col-5 text-muted">{{ __('owner.sup.assigned') }}</dt>
                    <dd class="col-7 mb-0">{{ $ticket->assignee?->name ?? '—' }}</dd>

                    <dt class="col-5 text-muted">{{ __('common.created') }}</dt>
                    <dd class="col-7 mb-0">{{ $ticket->created_at->format('M d, Y H:i') }}</dd>

                    @if($ticket->closed_at)
                        <dt class="col-5 text-muted">{{ __('owner.sup.resolved') }}</dt>
                        <dd class="col-7 mb-0">{{ $ticket->closed_at->format('M d, Y H:i') }}</dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
