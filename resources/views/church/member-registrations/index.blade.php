@extends('layouts.church')

@section('title', __('pages.member_registrations.title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-user-plus',
    'title' => __('pages.member_registrations.title'),
    'subtitle' => __('pages.member_registrations.subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.registration_approvals')],
    ],
])

@if($pendingCount > 0)
    <div class="mb-3">
        <span class="badge badge-warning" style="font-size: 1rem;">{{ __('pages.member_registrations.pending_badge', ['count' => $pendingCount]) }}</span>
    </div>
@endif

@include('partials.member-registration-link')

<div class="tile mb-3">
    <form method="GET" class="form-row align-items-end">
        <div class="col-md-4">
            <label class="small text-muted">{{ __('common.search') }}</label>
            <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('pages.member_registrations.search_placeholder') }}">
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
            @if($canFilterBranches && $branches->count() > 1)
                <label class="small text-muted">{{ __('common.branch') }}</label>
                <select name="branch_id" class="form-control">
                    <option value="">{{ __('pages.shared.all_branches') }}</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) ($filters['branch_id'] ?? '') === (string) $branch->id)>{{ $branch->displayLabel() }}</option>
                    @endforeach
                </select>
            @endif
        </div>
        <div class="col-md-2">
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
                    <th>{{ __('pages.shared.applicant') }}</th>
                    <th>{{ __('common.phone') }}</th>
                    <th>{{ __('common.branch') }}</th>
                    <th>{{ __('common.status') }}</th>
                    <th>{{ __('pages.shared.submitted') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($applications as $application)
                    <tr>
                        <td><code>{{ $application->application_number }}</code></td>
                        <td>{{ $application->full_name }}</td>
                        <td>{{ $application->phone_number ?? '—' }}</td>
                        <td>{{ $application->branch?->displayLabel() ?? '—' }}</td>
                        <td><span class="badge badge-{{ $application->status->badgeClass() }}">{{ $application->status->label() }}</span></td>
                        <td>{{ $application->created_at?->format('M d, Y') }}</td>
                        <td class="text-right">
                            <a href="{{ route('church.member-registrations.show', $application) }}" class="btn btn-sm btn-info">{{ __('common.review') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted">{{ __('pages.member_registrations.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $applications->links() }}</div>
</div>
@endsection
