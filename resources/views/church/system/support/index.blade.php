@extends('layouts.church')

@section('title', __('pages.support.title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-life-ring',
    'title' => __('pages.support.title'),
    'subtitle' => __('pages.support.subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.support_tickets')],
    ],
])

<div class="mb-3 text-right">
    <a href="{{ route('church.system.support.create') }}" class="btn btn-primary">
        <i class="fa fa-plus"></i> {{ __('pages.support.create') }}
    </a>
</div>

<div class="row mb-3">
    <div class="col-md-4">
        <div class="widget-small warning coloured-icon">
            <i class="icon fa fa-folder-open fa-3x"></i>
            <div class="info"><h4>{{ __('pages.support.open') }}</h4><p><b>{{ $stats['open'] }}</b></p></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="widget-small info coloured-icon">
            <i class="icon fa fa-spinner fa-3x"></i>
            <div class="info"><h4>{{ __('pages.support.in_progress') }}</h4><p><b>{{ $stats['in_progress'] }}</b></p></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="widget-small success coloured-icon">
            <i class="icon fa fa-check fa-3x"></i>
            <div class="info"><h4>{{ __('pages.support.resolved') }}</h4><p><b>{{ $stats['resolved'] }}</b></p></div>
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
                    <th>{{ __('pages.support.subject') }}</th>
                    <th>{{ __('pages.support.category') }}</th>
                    <th>{{ __('pages.support.priority') }}</th>
                    <th>{{ __('pages.support.status') }}</th>
                    <th>{{ __('common.created') }}</th>
                    <th>{{ __('common.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                    <tr>
                        <td>{{ $ticket->subject }}</td>
                        <td>{{ __('pages.support.categories.'.$ticket->category) }}</td>
                        <td><span class="badge badge-secondary">{{ __('pages.support.priorities.'.$ticket->priority) }}</span></td>
                        <td>{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</td>
                        <td>{{ $ticket->created_at->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('church.system.support.show', $ticket) }}" class="btn btn-sm btn-outline-primary">
                                {{ __('pages.support.view') }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="fa fa-ticket fa-2x d-block mb-2"></i>
                            {{ __('pages.support.no_tickets') }}
                            <div class="mt-3">
                                <a href="{{ route('church.system.support.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fa fa-plus"></i> {{ __('pages.support.create') }}
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $tickets->links() }}
</div>
@endsection
