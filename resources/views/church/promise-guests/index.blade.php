@extends('layouts.church')

@section('title', 'Promise & Temporary Guests')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-user-plus"></i> Promise & Temporary Guests</h1>
        <p>Register guests and send SMS invitations or reminders</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">Guests</li>
    </ul>
</div>

<div class="row mb-3">
    <div class="col-md-3"><div class="tile text-center p-3"><h4>{{ $stats['total'] }}</h4><small class="text-muted">Total</small></div></div>
    <div class="col-md-3"><div class="tile text-center p-3"><h4 class="text-warning">{{ $stats['pending'] }}</h4><small class="text-muted">Pending</small></div></div>
    <div class="col-md-3"><div class="tile text-center p-3"><h4 class="text-info">{{ $stats['notified'] }}</h4><small class="text-muted">Notified</small></div></div>
    <div class="col-md-3"><div class="tile text-center p-3"><h4 class="text-success">{{ $stats['attended'] }}</h4><small class="text-muted">Attended</small></div></div>
</div>

<div class="row mb-3">
    <div class="col-md-9">
        <form method="GET" class="form-inline">
            <input type="text" name="search" class="form-control mr-2 mb-2" placeholder="Search name or phone..."
                value="{{ $filters['search'] ?? '' }}">
            <select name="guest_type" class="form-control mr-2 mb-2">
                <option value="">All types</option>
                @foreach($guestTypes as $type)
                    <option value="{{ $type->value }}" @selected(($filters['guest_type'] ?? '') === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
            <select name="status" class="form-control mr-2 mb-2">
                <option value="">All statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <input type="date" name="from" class="form-control mr-2 mb-2" value="{{ $filters['from'] ?? '' }}">
            <input type="date" name="to" class="form-control mr-2 mb-2" value="{{ $filters['to'] ?? '' }}">
            <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-search"></i> Filter</button>
        </form>
    </div>
    <div class="col-md-3 text-md-right">
        @can('create', \App\Models\PromiseGuest::class)
            <a href="{{ route('church.promise-guests.create') }}" class="btn btn-primary mb-2">
                <i class="fa fa-plus"></i> Add Guest
            </a>
        @endcan
    </div>
</div>

<div class="tile">
    <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Type</th>
                    <th>Event / Date</th>
                    <th>Status</th>
                    <th width="160">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($guests as $guest)
                    <tr>
                        <td>{{ $guest->name }}</td>
                        <td>{{ $guest->phone_number }}</td>
                        <td><span class="badge badge-light">{{ $guest->guest_type->label() }}</span></td>
                        <td>
                            {{ $guest->eventLabel() }}
                            @if($guest->notified_at)
                                <br><small class="text-muted">SMS: {{ $guest->notified_at->format('M d, H:i') }}</small>
                            @endif
                        </td>
                        <td><span class="badge badge-{{ $guest->status->badgeClass() }}">{{ $guest->status->label() }}</span></td>
                        <td>
                            <a href="{{ route('church.promise-guests.show', $guest) }}" class="btn btn-sm btn-info" title="View"><i class="fa fa-eye"></i></a>
                            @can('sendSms', $guest)
                                <form method="POST" action="{{ route('church.promise-guests.send-sms', $guest) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success"
                                        title="{{ $guest->status === \App\Enums\PromiseGuestStatus::Attended ? 'Send welcome back SMS' : 'Send SMS reminder' }}">
                                        <i class="fa fa-comment"></i>
                                    </button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-muted text-center py-4">
                            No guests registered yet.
                            @can('create', \App\Models\PromiseGuest::class)
                                <a href="{{ route('church.promise-guests.create') }}">Add a guest</a>.
                            @endcan
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $guests->links() }}
</div>
@endsection
