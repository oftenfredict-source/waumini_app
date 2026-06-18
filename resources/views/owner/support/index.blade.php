@extends('layouts.owner')

@section('title', 'Support')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-life-ring"></i> Support Tickets</h1>
        <p>Church support requests and issues</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('owner.dashboard') }}">Overview</a></li>
        <li class="breadcrumb-item">Support</li>
    </ul>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="widget-small warning coloured-icon">
            <i class="icon fa fa-folder-open fa-3x"></i>
            <div class="info"><h4>Open</h4><p><b>{{ $stats['open'] }}</b></p></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="widget-small info coloured-icon">
            <i class="icon fa fa-spinner fa-3x"></i>
            <div class="info"><h4>In Progress</h4><p><b>{{ $stats['in_progress'] }}</b></p></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="widget-small success coloured-icon">
            <i class="icon fa fa-check fa-3x"></i>
            <div class="info"><h4>Resolved</h4><p><b>{{ $stats['resolved'] }}</b></p></div>
        </div>
    </div>
</div>

<div class="tile">
    <form method="GET" class="form-inline mb-3">
        <select name="status" class="form-control mr-2">
            <option value="">All statuses</option>
            @foreach(['open','in_progress','waiting','resolved','closed'] as $s)
                <option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>

    <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Church</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Assigned</th>
                    <th>Created</th>
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
                            No support tickets yet. Tickets from churches will appear here.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $tickets->links() }}
</div>
@endsection
