@extends('layouts.church')

@section('title', $guest->name)

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-user"></i> {{ $guest->name }}</h1>
        <p>{{ $guest->guest_type->label() }} — {{ $guest->eventLabel() }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.promise-guests.index') }}">Guests</a></li>
        <li class="breadcrumb-item">Details</li>
    </ul>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="tile">
            <h3 class="tile-title">Guest Information</h3>
            <table class="table table-borderless table-sm">
                <tr><th width="180">Type</th><td>{{ $guest->guest_type->label() }}</td></tr>
                <tr><th>Status</th><td><span class="badge badge-{{ $guest->status->badgeClass() }}">{{ $guest->status->label() }}</span></td></tr>
                <tr><th>Phone</th><td>{{ $guest->phone_number }}</td></tr>
                <tr><th>Email</th><td>{{ $guest->email ?? '—' }}</td></tr>
                <tr><th>Visit Date</th><td>{{ $guest->promised_date->format('M d, Y') }}</td></tr>
                <tr><th>Linked To</th><td>{{ $guest->eventLabel() }}</td></tr>
                <tr><th>Notes</th><td>{{ $guest->notes ?? '—' }}</td></tr>
                <tr><th>Registered By</th><td>{{ $guest->creator?->name ?? '—' }}</td></tr>
                <tr><th>Registered On</th><td>{{ $guest->created_at->format('M d, Y H:i') }}</td></tr>
                @if($guest->notified_at)
                    <tr><th>SMS Sent</th><td>{{ $guest->notified_at->format('M d, Y H:i') }}</td></tr>
                @endif
            </table>
        </div>
    </div>
    <div class="col-md-4">
        <div class="tile">
            <h3 class="tile-title">Actions</h3>
            <a href="{{ route('church.promise-guests.index') }}" class="btn btn-secondary btn-block mb-2">
                <i class="fa fa-arrow-left"></i> Back to Guests
            </a>
            @can('update', $guest)
                <a href="{{ route('church.promise-guests.edit', $guest) }}" class="btn btn-primary btn-block mb-2">
                    <i class="fa fa-pencil"></i> Edit Guest
                </a>
            @endcan
            @can('sendSms', $guest)
                <form method="POST" action="{{ route('church.promise-guests.send-sms', $guest) }}" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fa fa-comment"></i>
                        @if($guest->status === \App\Enums\PromiseGuestStatus::Attended)
                            Send Welcome Back SMS
                        @else
                            Send SMS Reminder
                        @endif
                    </button>
                </form>
            @endcan
            @can('update', $guest)
                @if($guest->status !== \App\Enums\PromiseGuestStatus::Attended)
                    <form method="POST" action="{{ route('church.promise-guests.mark-attended', $guest) }}" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-outline-success btn-block">
                            <i class="fa fa-check"></i> Mark as Attended
                        </button>
                    </form>
                @endif
            @endcan
            @can('delete', $guest)
                <form method="POST" action="{{ route('church.promise-guests.destroy', $guest) }}"
                    data-swal-confirm="Delete this guest record?" data-swal-delete>
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block">
                        <i class="fa fa-trash"></i> Delete Guest
                    </button>
                </form>
            @endcan
        </div>
    </div>
</div>
@endsection
