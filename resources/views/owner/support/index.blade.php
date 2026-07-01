@extends('layouts.owner')

@section('title', __('owner.sup.title'))

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-life-ring"></i> {{ __('owner.sup.heading') }}</h1>
        <p>{{ __('owner.sup.subtitle') }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('owner.dashboard') }}">{{ __('owner.overview') }}</a></li>
        <li class="breadcrumb-item">{{ __('owner.support') }}</li>
    </ul>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="widget-small warning coloured-icon">
            <i class="icon fa fa-folder-open fa-3x"></i>
            <div class="info"><h4>{{ __('owner.sup.open') }}</h4><p><b>{{ $stats['open'] }}</b></p></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="widget-small info coloured-icon">
            <i class="icon fa fa-spinner fa-3x"></i>
            <div class="info"><h4>{{ __('owner.sup.in_progress') }}</h4><p><b>{{ $stats['in_progress'] }}</b></p></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="widget-small success coloured-icon">
            <i class="icon fa fa-check fa-3x"></i>
            <div class="info"><h4>{{ __('owner.sup.resolved') }}</h4><p><b>{{ $stats['resolved'] }}</b></p></div>
        </div>
    </div>
</div>

<div class="tile">
    <form method="GET" class="form-inline mb-3">
        <select name="status" class="form-control mr-2">
            <option value="">{{ __('pages.shared.all_statuses') }}</option>
            @foreach(['open','in_progress','waiting','resolved','closed'] as $s)
                <option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary">{{ __('common.filter') }}</button>
    </form>

    <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th>{{ __('common.subject') }}</th>
                    <th>{{ __('owner.church_label') }}</th>
                    <th>{{ __('owner.sup.category') }}</th>
                    <th>{{ __('owner.sup.priority') }}</th>
                    <th>{{ __('owner.status') }}</th>
                    <th>{{ __('owner.sup.assigned') }}</th>
                    <th>{{ __('common.created') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                    <tr>
                        <td>{{ $ticket->subject }}</td>
                        <td>{{ $ticket->church?->name }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $ticket->category)) }}</td>
                        <td><span class="badge badge-secondary">{{ ucfirst($ticket->priority) }}</span></td>
                        <td>{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</td>
                        <td>{{ $ticket->assignee?->name ?? '—' }}</td>
                        <td>{{ $ticket->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="fa fa-ticket fa-2x d-block mb-2"></i>
                            {{ __('owner.sup.no_tickets') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $tickets->links() }}
</div>
@endsection
