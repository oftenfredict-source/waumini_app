@extends('layouts.church')

@section('title', $event->title)

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-star',
    'title' => $event->title,
    'subtitle' => $event->event_date->format('l, M d, Y'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.special_events'), 'route' => 'church.special-events.index'],
        ['label' => $event->title],
    ],
])

<div class="row">
    <div class="col-md-8">
        <div class="tile">
            <h3 class="tile-title">{{ __('pages.shared.event_details') }}</h3>
            <table class="table table-borderless table-sm">
                <tr>
                    <th width="180">{{ __('common.category') }}</th>
                    <td>
                        <span class="badge badge-{{ $event->category->badgeClass() }}">
                            {{ $event->categoryLabel() }}
                        </span>
                    </td>
                </tr>
                <tr><th>{{ __('common.date') }}</th><td>{{ $event->event_date->format('M d, Y') }}</td></tr>
                <tr>
                    <th>{{ __('pages.shared.time') }}</th>
                    <td>
                        @if($event->start_time)
                            {{ \Illuminate\Support\Str::of($event->start_time)->substr(0, 5) }}
                            @if($event->end_time)
                                – {{ \Illuminate\Support\Str::of($event->end_time)->substr(0, 5) }}
                            @endif
                        @else
                            —
                        @endif
                    </td>
                </tr>
                <tr><th>{{ __('pages.shared.speaker_guest') }}</th><td>{{ $event->speaker ?? '—' }}</td></tr>
                <tr><th>{{ __('common.venue') }}</th><td>{{ $event->venue ?? '—' }}</td></tr>
                <tr>
                    <th>{{ __('pages.shared.budget') }}</th>
                    <td>{{ $event->budget_amount !== null ? 'TZS '.number_format($event->budget_amount, 2) : '—' }}</td>
                </tr>
                <tr><th>{{ __('pages.shared.expected_attendance') }}</th><td>{{ $event->expected_attendance ?? '—' }}</td></tr>
                <tr>
                    <th>{{ __('common.status') }}</th>
                    <td>
                        <span class="badge badge-{{ $event->status->badgeClass() }}">
                            {{ $event->status->label() }}
                        </span>
                    </td>
                </tr>
                <tr><th>{{ __('common.description') }}</th><td>{{ $event->description ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.notes') }}</th><td>{{ $event->notes ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.created_by') }}</th><td>{{ $event->creator?->name ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.recorded_on') }}</th><td>{{ $event->created_at->format('M d, Y H:i') }}</td></tr>
            </table>
        </div>
    </div>
    <div class="col-md-4">
        <div class="tile">
            <h3 class="tile-title">{{ __('common.actions') }}</h3>
            <a href="{{ route('church.special-events.index') }}" class="btn btn-secondary btn-block">
                <i class="fa fa-arrow-left"></i> {{ __('pages.special_events.back_to') }}
            </a>
            @can('update', $event)
                <a href="{{ route('church.special-events.edit', $event) }}" class="btn btn-primary btn-block mt-2">
                    <i class="fa fa-pencil"></i> {{ __('pages.special_events.edit_event') }}
                </a>
            @endcan
            @can('delete', $event)
                <form method="POST" action="{{ route('church.special-events.destroy', $event) }}" class="mt-2"
                    data-swal-confirm="{{ __('pages.special_events.delete_confirm') }}"
                    data-swal-delete
                    data-swal-confirm-text="{{ __('common.yes_delete') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block">
                        <i class="fa fa-trash"></i> {{ __('pages.special_events.delete_event') }}
                    </button>
                </form>
            @endcan
        </div>
    </div>
</div>
@endsection
