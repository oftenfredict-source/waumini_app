@extends('layouts.church')

@section('title', $service->displayTitle())

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-calendar',
    'title' => $service->displayTitle(),
    'subtitle' => $service->service_date?->format('l, M d, Y'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.member.dashboard'],
        ['label' => __('pages.member_portal_services.title'), 'route' => 'church.member.services.index'],
        ['label' => $service->displayTitle()],
    ],
])

<div class="row">
    <div class="col-lg-8">
        <div class="tile">
            <h3 class="tile-title">{{ __('pages.services.service_details') }}</h3>
            <table class="table table-borderless table-sm mb-0">
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
                <tr><th>{{ __('common.date') }}</th><td>{{ $service->service_date?->format('l, M d, Y') ?? '—' }}</td></tr>
                <tr>
                    <th>{{ __('pages.shared.time') }}</th>
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
                </tr>
                @if($church->branches_enabled)
                    <tr><th>{{ __('common.branch') }}</th><td>{{ $service->branch?->name ?? '—' }}</td></tr>
                @endif
                <tr><th>{{ __('pages.shared.theme') }}</th><td>{{ $service->theme ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.services.preacher_speaker') }}</th><td>{{ $service->preacherDisplay() }}</td></tr>
                <tr><th>{{ __('pages.services.coordinator') }}</th><td>{{ $service->coordinatorDisplay() }}</td></tr>
                <tr><th>{{ __('common.venue') }}</th><td>{{ $service->venue ?? '—' }}</td></tr>
                <tr>
                    <th>{{ __('common.status') }}</th>
                    <td>
                        <span class="badge badge-{{ $service->status->badgeClass() }}">
                            {{ $service->status->label() }}
                        </span>
                    </td>
                </tr>
                @if($service->notes)
                    <tr><th>{{ __('pages.shared.notes') }}</th><td style="white-space: pre-wrap;">{{ $service->notes }}</td></tr>
                @endif
            </table>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="tile">
            <a href="{{ route('church.member.services.index') }}" class="btn btn-secondary btn-block">
                <i class="fa fa-arrow-left"></i> {{ __('pages.member_portal_services.back_to_services') }}
            </a>
            @if(! $service->isSundaySchool() && $service->status->value !== 'cancelled')
                <a href="{{ \App\Services\Church\AttendanceService::scanUrl(\App\Enums\AttendanceSourceType::ChurchService->value, $service->id) }}"
                   class="btn btn-success btn-block mt-2">
                    <i class="fa fa-qrcode"></i> {{ __('pages.attendance.checkin_button') }}
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
