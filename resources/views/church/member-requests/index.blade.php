@extends('layouts.church')

@section('title', __('pages.member_requests.title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-envelope-open',
    'title' => __('pages.member_requests.title'),
    'subtitle' => __('pages.member_requests.subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.member_requests')],
    ],
])

<div class="tile mb-3">
    <form method="GET" class="form-row align-items-end">
        <div class="col-md-4">
            <label class="small text-muted">{{ __('common.search') }}</label>
            <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('pages.member_requests.search_placeholder') }}">
        </div>
        <div class="col-md-3">
            <label class="small text-muted">{{ __('common.status') }}</label>
            <select name="status" class="form-control">
                <option value="">{{ __('pages.shared.all_statuses') }}</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="small text-muted">{{ __('common.filter') }}</label>
            <select name="filter" class="form-control">
                <option value="">{{ __('pages.shared.all_requests') }}</option>
                <option value="mine" @selected(($filters['filter'] ?? '') === 'mine')>{{ __('pages.shared.assigned_to_me') }}</option>
            </select>
        </div>
        <div class="col-md-2">
            @if($canFilterBranches && $branches->count() > 1)
                <label class="small text-muted">{{ __('common.branch') }}</label>
                <select name="branch_id" class="form-control mb-2">
                    <option value="">{{ __('pages.shared.all_branches') }}</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) ($filters['branch_id'] ?? '') === (string) $branch->id)>{{ $branch->displayLabel() }}</option>
                    @endforeach
                </select>
            @endif
            <button type="submit" class="btn btn-primary btn-block">{{ __('common.filter') }}</button>
        </div>
    </form>
</div>

<div class="tile">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>{{ __('common.reference') }}</th>
                    <th>{{ __('common.member') }}</th>
                    <th>{{ __('common.type') }}</th>
                    <th>{{ __('common.subject') }}</th>
                    <th>{{ __('pages.shared.assigned_leader') }}</th>
                    <th>{{ __('common.status') }}</th>
                    <th>{{ __('common.date') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $item)
                    <tr>
                        <td><code>{{ $item->reference_number }}</code></td>
                        <td>{{ $item->member?->full_name }}</td>
                        <td>{{ $item->type->label() }}</td>
                        <td>{{ Str::limit($item->subject, 35) }}</td>
                        <td>{{ $item->assignedLeader?->member?->full_name ?? '—' }}<br><small class="text-muted">{{ $item->assignedLeader?->positionLabel() }}</small></td>
                        <td><span class="badge badge-{{ $item->status->badgeClass() }}">{{ $item->status->label() }}</span></td>
                        <td>{{ $item->created_at?->format('M d, Y') }}</td>
                        <td class="text-right">
                            <a href="{{ route('church.member-requests.show', $item) }}" class="btn btn-sm btn-info">{{ __('common.view') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-muted">{{ __('pages.member_requests.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($requests->hasPages())
        <div class="mt-3">{{ $requests->links() }}</div>
    @endif
</div>
@endsection
