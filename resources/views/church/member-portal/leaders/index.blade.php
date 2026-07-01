@extends('layouts.church')

@section('title', __('pages.member_portal_leaders.title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-star',
    'title' => __('pages.member_portal_leaders.title'),
    'subtitle' => __('pages.member_portal_leaders.subtitle', ['church' => $church->name]),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.member.dashboard'],
        ['label' => __('menu.leaders')],
    ],
])

<div class="tile">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>{{ __('common.name') }}</th>
                    <th>{{ __('pages.shared.position') }}</th>
                    <th>{{ __('pages.shared.member_id') }}</th>
                    <th>{{ __('pages.shared.appointed') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaders as $leader)
                    <tr>
                        <td>{{ $leader->member?->full_name ?? '—' }}</td>
                        <td>{{ $leader->positionLabel() }}</td>
                        <td><code>{{ $leader->member?->member_number ?? '—' }}</code></td>
                        <td>{{ $leader->appointment_date?->format('M d, Y') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted">{{ __('pages.member_portal_leaders.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
