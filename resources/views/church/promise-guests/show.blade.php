@extends('layouts.church')

@section('title', $guest->name)

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-user',
    'title' => $guest->name,
    'subtitle' => $guest->guest_type->label() . ' — ' . $guest->eventLabel(),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('pages.promise_guests.breadcrumb'), 'route' => 'church.promise-guests.index'],
        ['label' => __('pages.shared.breadcrumb_details')],
    ],
])

<div class="row">
    <div class="col-md-8">
        <div class="tile">
            <h3 class="tile-title">{{ __('pages.shared.guest_information') }}</h3>
            <table class="table table-borderless table-sm">
                <tr><th width="180">{{ __('common.type') }}</th><td>{{ $guest->guest_type->label() }}</td></tr>
                <tr><th>{{ __('common.status') }}</th><td><span class="badge badge-{{ $guest->status->badgeClass() }}">{{ $guest->status->label() }}</span></td></tr>
                <tr><th>{{ __('common.phone') }}</th><td>{{ $guest->phone_number }}</td></tr>
                <tr><th>{{ __('common.email') }}</th><td>{{ $guest->email ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.visit_date') }}</th><td>{{ $guest->promised_date->format('M d, Y') }}</td></tr>
                <tr><th>{{ __('pages.shared.linked_to') }}</th><td>{{ $guest->eventLabel() }}</td></tr>
                <tr><th>{{ __('pages.shared.notes') }}</th><td>{{ $guest->notes ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.registered_by') }}</th><td>{{ $guest->creator?->name ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.registered_on') }}</th><td>{{ $guest->created_at->format('M d, Y H:i') }}</td></tr>
                @if($guest->notified_at)
                    <tr><th>{{ __('pages.shared.sms_sent') }}</th><td>{{ $guest->notified_at->format('M d, Y H:i') }}</td></tr>
                @endif
            </table>
        </div>
    </div>
    <div class="col-md-4">
        <div class="tile">
            <h3 class="tile-title">{{ __('common.actions') }}</h3>
            <a href="{{ route('church.promise-guests.index') }}" class="btn btn-secondary btn-block mb-2">
                <i class="fa fa-arrow-left"></i> {{ __('pages.promise_guests.back_to') }}
            </a>
            @can('update', $guest)
                <a href="{{ route('church.promise-guests.edit', $guest) }}" class="btn btn-primary btn-block mb-2">
                    <i class="fa fa-pencil"></i> {{ __('pages.promise_guests.edit_guest') }}
                </a>
            @endcan
            @can('sendSms', $guest)
                <form method="POST" action="{{ route('church.promise-guests.send-sms', $guest) }}" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fa fa-comment"></i>
                        @if($guest->status === \App\Enums\PromiseGuestStatus::Attended)
                            {{ __('pages.promise_guests.welcome_back_sms') }}
                        @else
                            {{ __('pages.promise_guests.send_reminder_sms') }}
                        @endif
                    </button>
                </form>
            @endcan
            @can('update', $guest)
                @if($guest->status !== \App\Enums\PromiseGuestStatus::Attended)
                    <form method="POST" action="{{ route('church.promise-guests.mark-attended', $guest) }}" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-outline-success btn-block">
                            <i class="fa fa-check"></i> {{ __('pages.promise_guests.mark_attended') }}
                        </button>
                    </form>
                @endif
            @endcan
            @can('delete', $guest)
                <form method="POST" action="{{ route('church.promise-guests.destroy', $guest) }}"
                    data-swal-confirm="{{ __('pages.promise_guests.delete_confirm') }}" data-swal-delete>
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block">
                        <i class="fa fa-trash"></i> {{ __('pages.promise_guests.delete_guest') }}
                    </button>
                </form>
            @endcan
        </div>
    </div>
</div>
@endsection
