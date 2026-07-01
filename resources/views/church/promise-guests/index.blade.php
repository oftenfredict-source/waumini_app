@extends('layouts.church')

@section('title', __('pages.promise_guests.title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-user-plus',
    'title' => __('pages.promise_guests.title'),
    'subtitle' => __('pages.promise_guests.subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('pages.promise_guests.breadcrumb')],
    ],
])

<div class="row mb-3">
    <div class="col-md-3"><div class="tile text-center p-3"><h4>{{ $stats['total'] }}</h4><small class="text-muted">{{ __('common.total') }}</small></div></div>
    <div class="col-md-3"><div class="tile text-center p-3"><h4 class="text-warning">{{ $stats['pending'] }}</h4><small class="text-muted">{{ __('common.pending') }}</small></div></div>
    <div class="col-md-3"><div class="tile text-center p-3"><h4 class="text-info">{{ $stats['notified'] }}</h4><small class="text-muted">{{ __('pages.shared.notified') }}</small></div></div>
    <div class="col-md-3"><div class="tile text-center p-3"><h4 class="text-success">{{ $stats['attended'] }}</h4><small class="text-muted">{{ __('pages.shared.attended') }}</small></div></div>
</div>

<div class="row mb-3">
    <div class="col-md-9">
        <form method="GET" class="form-inline">
            <input type="text" name="search" class="form-control mr-2 mb-2" placeholder="{{ __('pages.promise_guests.search_placeholder') }}"
                value="{{ $filters['search'] ?? '' }}">
            <select name="guest_type" class="form-control mr-2 mb-2">
                <option value="">{{ __('pages.shared.all_types') }}</option>
                @foreach($guestTypes as $type)
                    <option value="{{ $type->value }}" @selected(($filters['guest_type'] ?? '') === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
            <select name="status" class="form-control mr-2 mb-2">
                <option value="">{{ __('pages.shared.all_statuses') }}</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <input type="date" name="from" class="form-control mr-2 mb-2" value="{{ $filters['from'] ?? '' }}">
            <input type="date" name="to" class="form-control mr-2 mb-2" value="{{ $filters['to'] ?? '' }}">
            <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-search"></i> {{ __('common.filter') }}</button>
        </form>
    </div>
    <div class="col-md-3 text-md-right">
        @can('create', \App\Models\PromiseGuest::class)
            <a href="{{ route('church.promise-guests.create') }}" class="btn btn-primary mb-2">
                <i class="fa fa-plus"></i> {{ __('pages.promise_guests.add_guest') }}
            </a>
        @endcan
    </div>
</div>

<div class="tile">
    <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th>{{ __('common.name') }}</th>
                    <th>{{ __('common.phone') }}</th>
                    <th>{{ __('common.type') }}</th>
                    <th>{{ __('pages.shared.event_date') }}</th>
                    <th>{{ __('common.status') }}</th>
                    <th width="160">{{ __('common.actions') }}</th>
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
                                <br><small class="text-muted">{{ __('pages.promise_guests.sms_at', ['datetime' => $guest->notified_at->format('M d, H:i')]) }}</small>
                            @endif
                        </td>
                        <td><span class="badge badge-{{ $guest->status->badgeClass() }}">{{ $guest->status->label() }}</span></td>
                        <td>
                            <a href="{{ route('church.promise-guests.show', $guest) }}" class="btn btn-sm btn-info" title="{{ __('common.view') }}"><i class="fa fa-eye"></i></a>
                            @can('sendSms', $guest)
                                <form method="POST" action="{{ route('church.promise-guests.send-sms', $guest) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success"
                                        title="{{ $guest->status === \App\Enums\PromiseGuestStatus::Attended ? __('pages.promise_guests.welcome_back_sms') : __('pages.promise_guests.send_reminder_sms') }}">
                                        <i class="fa fa-comment"></i>
                                    </button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-muted text-center py-4">
                            {{ __('pages.promise_guests.empty') }}
                            @can('create', \App\Models\PromiseGuest::class)
                                <a href="{{ route('church.promise-guests.create') }}">{{ __('pages.promise_guests.add_link') }}</a>.
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
