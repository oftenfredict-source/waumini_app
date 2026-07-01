@extends('layouts.church')

@section('title', $leader->positionLabel())

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-star',
    'title' => $leader->positionLabel(),
    'subtitle' => __('pages.leadership.show_subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.leadership'), 'route' => 'church.leadership.index'],
        ['label' => $leader->member?->full_name],
    ],
])

<div class="row">
    <div class="col-md-8">
        <div class="tile">
            <h3 class="tile-title">{{ __('pages.leadership.assignment_details') }}</h3>
            <table class="table table-borderless">
                <tr><th width="180">{{ __('common.member') }}</th><td>{{ $leader->member?->full_name ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.member_number') }}</th><td><code>{{ $leader->member?->member_number ?? '—' }}</code></td></tr>
                <tr><th>{{ __('pages.shared.position') }}</th><td>{{ $leader->positionLabel() }}</td></tr>
                <tr><th>{{ __('pages.shared.appointment_date') }}</th><td>{{ $leader->appointment_date->format('M d, Y') }}</td></tr>
                <tr><th>{{ __('pages.shared.end_date') }}</th><td>{{ $leader->end_date?->format('M d, Y') ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.appointed_by') }}</th><td>{{ $leader->appointed_by ?? '—' }}</td></tr>
                <tr><th>{{ __('common.status') }}</th>
                    <td>
                        <span class="badge badge-{{ $leader->isCurrentlyActive() ? 'success' : 'secondary' }}">
                            {{ $leader->isCurrentlyActive() ? __('common.active') : __('common.inactive') }}
                        </span>
                    </td>
                </tr>
                <tr><th>{{ __('common.description') }}</th><td>{{ $leader->description ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.notes') }}</th><td>{{ $leader->notes ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.assigned_on') }}</th><td>{{ $leader->created_at->format('M d, Y H:i') }}</td></tr>
            </table>
        </div>
    </div>
    <div class="col-md-4">
        <div class="tile">
            <h3 class="tile-title">{{ __('common.actions') }}</h3>
            <a href="{{ route('church.leadership.index') }}" class="btn btn-secondary btn-block mb-2">
                <i class="fa fa-arrow-left"></i> {{ __('pages.shared.back_to', ['module' => __('pages.leadership.title')]) }}
            </a>
            @if($leader->member)
                <a href="{{ route('church.members.show', $leader->member) }}" class="btn btn-outline-primary btn-block mb-2">
                    <i class="fa fa-user"></i> {{ __('pages.leadership.view_member_profile') }}
                </a>
            @endif
            @can('deactivate', $leader)
                @if($leader->isCurrentlyActive())
                    <form method="POST" action="{{ route('church.leadership.deactivate', $leader) }}"
                        data-swal-confirm="{{ __('pages.leadership.end_assignment_confirm') }}">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-block">
                            <i class="fa fa-ban"></i> {{ __('pages.leadership.end_assignment') }}
                        </button>
                    </form>
                @endif
            @endcan
        </div>
    </div>
</div>
@endsection
