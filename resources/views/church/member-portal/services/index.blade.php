@extends('layouts.church')

@section('title', __('pages.member_portal_services.title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-calendar',
    'title' => __('pages.member_portal_services.title'),
    'subtitle' => __('pages.member_portal_services.subtitle', ['church' => $church->name]),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.member.dashboard'],
        ['label' => __('menu.services')],
    ],
])

<div class="tile">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>{{ __('pages.shared.service') }}</th>
                    <th>{{ __('common.date') }}</th>
                    <th>{{ __('pages.shared.time') }}</th>
                    <th>{{ __('pages.shared.preacher') }}</th>
                    <th>{{ __('common.venue') }}</th>
                    <th>{{ __('pages.shared.theme') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($services as $service)
                    <tr>
                        <td>{{ $service->displayTitle() }}</td>
                        <td>{{ $service->service_date?->format('M d, Y') ?? '—' }}</td>
                        <td>
                            @if($service->start_time)
                                {{ \Illuminate\Support\Carbon::parse($service->start_time)->format('g:i A') }}
                                @if($service->end_time)
                                    – {{ \Illuminate\Support\Carbon::parse($service->end_time)->format('g:i A') }}
                                @endif
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $service->preacher ?? '—' }}</td>
                        <td>{{ $service->venue ?? '—' }}</td>
                        <td>{{ $service->theme ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted">{{ __('pages.member_portal_services.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
