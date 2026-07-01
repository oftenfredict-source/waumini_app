@extends('layouts.church')

@section('title', $service->displayTitle())

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-calendar',
    'title' => $service->displayTitle(),
    'subtitle' => $service->service_date->format('l, M d, Y'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.services'), 'route' => 'church.services.index'],
        ['label' => $service->displayTitle()],
    ],
])

<div class="row">
    <div class="col-md-8">
        <div class="tile">
            <h3 class="tile-title">{{ __('pages.services.service_details') }}</h3>
            <table class="table table-borderless table-sm">
                <tr>
                    <th width="180">{{ __('common.type') }}</th>
                    <td>
                        <span class="badge badge-{{ $service->service_type->badgeClass() }}">
                            {{ $service->service_type->label() }}
                        </span>
                    </td>
                </tr>
                @if($service->title)
                    <tr><th>{{ __('common.title') }}</th><td>{{ $service->title }}</td></tr>
                @endif
                <tr><th>{{ __('common.date') }}</th><td>{{ $service->service_date->format('M d, Y') }}</td></tr>
                <tr>
                    <th>{{ __('pages.shared.time') }}</th>
                    <td>
                        @if($service->start_time)
                            {{ \Illuminate\Support\Str::of($service->start_time)->substr(0, 5) }}
                            @if($service->end_time)
                                – {{ \Illuminate\Support\Str::of($service->end_time)->substr(0, 5) }}
                            @endif
                        @else
                            —
                        @endif
                    </td>
                </tr>
                <tr><th>{{ __('pages.shared.theme') }}</th><td>{{ $service->theme ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.services.preacher_speaker') }}</th><td>{{ $service->preacher ?? '—' }}</td></tr>
                <tr><th>{{ __('common.venue') }}</th><td>{{ $service->venue ?? '—' }}</td></tr>
                <tr>
                    <th>{{ __('common.status') }}</th>
                    <td>
                        <span class="badge badge-{{ $service->status->badgeClass() }}">
                            {{ $service->status->label() }}
                        </span>
                    </td>
                </tr>
                <tr><th>{{ __('pages.shared.notes') }}</th><td>{{ $service->notes ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.created_by') }}</th><td>{{ $service->creator?->name ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.recorded_on') }}</th><td>{{ $service->created_at->format('M d, Y H:i') }}</td></tr>
            </table>
        </div>
    </div>
    <div class="col-md-4">
        <div class="tile">
            <h3 class="tile-title">{{ __('common.actions') }}</h3>
            <a href="{{ route('church.services.index') }}" class="btn btn-secondary btn-block">
                <i class="fa fa-arrow-left"></i> {{ __('pages.shared.back_to', ['module' => __('pages.services.title')]) }}
            </a>
            @can('update', $service)
                <a href="{{ route('church.services.edit', $service) }}" class="btn btn-primary btn-block mt-2">
                    <i class="fa fa-pencil"></i> {{ __('pages.shared.edit_item', ['item' => __('pages.services.item')]) }}
                </a>
            @endcan
            @can('delete', $service)
                <form method="POST" action="{{ route('church.services.destroy', $service) }}" class="mt-2"
                    data-swal-confirm="{{ __('pages.services.delete_service_confirm') }}"
                    data-swal-delete
                    data-swal-confirm-text="{{ __('common.yes_delete') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block">
                        <i class="fa fa-trash"></i> {{ __('pages.shared.delete_item', ['item' => __('pages.services.item')]) }}
                    </button>
                </form>
            @endcan
        </div>
    </div>
</div>
@endsection
