@extends('layouts.church')

@section('title', __('pages.member_portal_requests.title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-envelope',
    'title' => __('pages.member_portal_requests.title'),
    'subtitle' => __('pages.member_portal_requests.subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.member.dashboard'],
        ['label' => __('menu.my_requests')],
    ],
])

<div class="mb-3 text-md-right">
    <a href="{{ route('church.member.requests.create') }}" class="btn btn-primary">
        <i class="fa fa-plus"></i> {{ __('pages.member_portal_requests.new_request') }}
    </a>
</div>

<div class="tile">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>{{ __('common.reference') }}</th>
                    <th>{{ __('common.type') }}</th>
                    <th>{{ __('common.subject') }}</th>
                    <th>{{ __('pages.shared.assigned_to') }}</th>
                    <th>{{ __('common.status') }}</th>
                    <th>{{ __('pages.shared.submitted') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $item)
                    <tr>
                        <td><code>{{ $item->reference_number }}</code></td>
                        <td>{{ $item->type->label() }}</td>
                        <td>{{ Str::limit($item->subject, 40) }}</td>
                        <td>{{ $item->assignedLeader?->member?->full_name ?? '—' }}<br><small class="text-muted">{{ $item->assignedLeader?->positionLabel() }}</small></td>
                        <td><span class="badge badge-{{ $item->status->badgeClass() }}">{{ $item->status->label() }}</span></td>
                        <td>{{ $item->created_at?->format('M d, Y') }}</td>
                        <td class="text-right text-nowrap">
                            <a href="{{ route('church.member.requests.show', $item) }}" class="btn btn-sm btn-info">{{ __('common.view') }}</a>
                            @if($item->hasDownloadableCertificate())
                                <a href="{{ route('church.member.requests.certificate', $item) }}" class="btn btn-sm btn-success" title="{{ __('pages.member_portal_requests.download_certificate') }}">
                                    <i class="fa fa-download"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted">{{ __('pages.member_portal_requests.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($requests->hasPages())
        <div class="mt-3">{{ $requests->links() }}</div>
    @endif
</div>
@endsection
