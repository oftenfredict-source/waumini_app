@extends('layouts.owner')

@section('title', __('owner.sup.title'))

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">{{ __('owner.sup.heading') }}</h1>
        <p class="text-muted mb-0">{{ __('owner.sup.subtitle') }}</p>
    </div>
    <form method="GET" class="d-flex flex-wrap align-items-center gap-2">
        <label class="small text-muted mb-0" for="status">{{ __('owner.status') }}</label>
        <select id="status" name="status" class="form-select form-select-sm" style="width:auto;">
            <option value="">{{ __('pages.shared.all_statuses') }}</option>
            @foreach(['open','in_progress','waiting','resolved','closed'] as $s)
                <option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>
                    {{ ucfirst(str_replace('_', ' ', $s)) }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="bi bi-funnel me-1"></i> {{ __('common.filter') }}
        </button>
    </form>
</div>

<div class="row g-3 g-lg-4 mb-4">
    <div class="col-sm-6 col-xl-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon" style="background:rgba(217,119,6,.12);color:#d97706;">
                        <i class="bi bi-folder2-open"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.sup.open') }}</p>
                        <div class="h3 mb-0">{{ number_format($stats['open']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon" style="background:rgba(8,145,178,.12);color:#0891b2;">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.sup.in_progress') }}</p>
                        <div class="h3 mb-0">{{ number_format($stats['in_progress']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon" style="background:rgba(22,163,74,.12);color:#16a34a;">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.sup.resolved') }}</p>
                        <div class="h3 mb-0">{{ number_format($stats['resolved']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card owner-panel-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>{{ __('owner.support') }}</span>
        <span class="badge text-bg-light border">{{ $tickets->total() }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ __('common.subject') }}</th>
                        <th>{{ __('owner.church_label') }}</th>
                        <th>{{ __('owner.sup.category') }}</th>
                        <th>{{ __('owner.sup.priority') }}</th>
                        <th>{{ __('owner.status') }}</th>
                        <th>{{ __('owner.sup.assigned') }}</th>
                        <th>{{ __('common.created') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
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
                        <tr>
                            <td>
                                <a href="{{ route('owner.support.show', $ticket) }}" class="fw-semibold text-decoration-none" style="color:#940000;">
                                    {{ $ticket->subject }}
                                </a>
                            </td>
                            <td>{{ $ticket->church?->name ?? '—' }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $ticket->category)) }}</td>
                            <td>
                                <span class="badge rounded-pill" style="{{ $priorityStyle }}">
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge rounded-pill" style="{{ $statusStyle }}">
                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                </span>
                            </td>
                            <td>{{ $ticket->assignee?->name ?? '—' }}</td>
                            <td class="text-nowrap">{{ $ticket->created_at->format('M d, Y') }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('owner.support.show', $ticket) }}" class="btn btn-sm btn-outline-primary">
                                    {{ __('owner.sup.view') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-life-preserver d-block mb-2" style="font-size:1.75rem;"></i>
                                {{ __('owner.sup.no_tickets') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($tickets->hasPages())
        <div class="card-footer bg-white">
            {{ $tickets->links() }}
        </div>
    @endif
</div>
@endsection
