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
                    <th>{{ __('common.status') }}</th>
                    <th class="text-right">{{ __('common.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($services as $service)
                    <tr>
                        <td>
                            <a href="{{ route('church.member.services.show', $service) }}">
                                {{ $service->displayTitle() }}
                            </a>
                        </td>
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
                        <td>{{ $service->preacherDisplay() }}</td>
                        <td>{{ $service->venue ?? '—' }}</td>
                        <td>
                            <span class="badge badge-{{ $service->status->badgeClass() }}">
                                {{ $service->status->label() }}
                            </span>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('church.member.services.show', $service) }}" class="btn btn-sm btn-info">
                                <i class="fa fa-eye"></i> {{ __('common.view') }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted">{{ __('pages.member_portal_services.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($services instanceof \Illuminate\Pagination\AbstractPaginator && $services->hasPages())
        <div class="tile-footer">
            {{ $services->links() }}
        </div>
    @endif
</div>
@endsection
