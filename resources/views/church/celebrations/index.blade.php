@extends('layouts.church')

@section('title', __('pages.celebrations.title'))

@section('content')
@php
    $monthLabel = $monthOptions[$displayMonth] ?? now()->format('F');
@endphp
@include('partials.page-header', [
    'icon' => 'fa fa-birthday-cake',
    'title' => __('pages.celebrations.title'),
    'subtitle' => __('pages.celebrations.subtitle', ['year' => $selectedYear, 'period' => $periodLabel]),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.celebrations')],
    ],
])

<div class="tile mb-3">
    <form method="GET" class="form-inline align-items-end flex-wrap">
        <div class="form-group mr-3 mb-2">
            <label class="small d-block mb-1">{{ __('pages.shared.year') }}</label>
            <select name="year" class="form-control form-control-sm" onchange="this.form.submit()">
                @foreach($yearOptions as $y)
                    <option value="{{ $y }}" @selected((int) $selectedYear === (int) $y)>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        @if(!empty($filters['period']))
            <input type="hidden" name="period" value="{{ $filters['period'] }}">
        @endif
        @if(!empty($filters['month']))
            <input type="hidden" name="month" value="{{ $filters['month'] }}">
        @endif
        <div class="form-group mb-2">
            <small class="text-muted d-block">
                {{ __('pages.celebrations.past_hidden_hint', ['year' => $selectedYear]) }}
                @if(!$isCurrentYear)
                    {{ __('pages.celebrations.viewing_year_hint') }}
                @endif
            </small>
        </div>
    </form>
</div>

<div class="row mb-3">
    <div class="col-md-3">
        <a href="{{ route('church.celebrations.index', ['year' => $selectedYear, 'period' => 'next_30_days']) }}" class="text-decoration-none">
            <div class="tile text-center p-3 h-100 {{ !$isCurrentYear ? 'opacity-50' : '' }}">
                <h4 class="text-warning mb-0">{{ $stats['next_30_days'] }}</h4>
                <small class="text-muted">{{ __('pages.celebrations.next_30_days') }}</small>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('church.celebrations.index', ['year' => $selectedYear, 'period' => 'this_month']) }}" class="text-decoration-none">
            <div class="tile text-center p-3 h-100">
                <h4 class="mb-0">{{ $stats['this_month'] }}</h4>
                <small class="text-muted">{{ $monthLabel }} {{ $selectedYear }}</small>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('church.celebrations.index', ['year' => $selectedYear, 'period' => 'this_month', 'celebration_type' => 'birthday']) }}" class="text-decoration-none">
            <div class="tile text-center p-3 h-100">
                <h4 class="text-info mb-0">{{ $stats['birthdays'] }}</h4>
                <small class="text-muted">{{ __('pages.celebrations.birthdays_in', ['month' => $monthLabel]) }}</small>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('church.celebrations.index', ['year' => $selectedYear, 'period' => 'this_month', 'celebration_type' => 'wedding_anniversary']) }}" class="text-decoration-none">
            <div class="tile text-center p-3 h-100">
                <h4 class="text-primary mb-0">{{ $stats['anniversaries'] }}</h4>
                <small class="text-muted">{{ __('pages.celebrations.anniversaries_in', ['month' => $monthLabel]) }}</small>
            </div>
        </a>
    </div>
</div>

<div class="tile mb-3">
    <h5 class="mb-2">{{ __('pages.celebrations.quick_period', ['year' => $selectedYear]) }}</h5>
    <div class="btn-group flex-wrap mb-2" role="group">
        <a href="{{ route('church.celebrations.index', ['year' => $selectedYear, 'period' => 'this_month']) }}"
            class="btn btn-sm {{ ($period ?? 'this_month') === 'this_month' && empty($filters['from']) && empty($filters['to']) && empty($filters['celebration_type']) ? 'btn-primary' : 'btn-outline-primary' }}">
            {{ $isCurrentYear ? now()->format('F') : ($monthOptions[$displayMonth] ?? __('pages.celebrations.month_fallback')) }}
        </a>
        @if($isCurrentYear)
            <a href="{{ route('church.celebrations.index', ['year' => $selectedYear, 'period' => 'next_30_days']) }}"
                class="btn btn-sm {{ ($period ?? '') === 'next_30_days' ? 'btn-primary' : 'btn-outline-primary' }}">
                {{ __('pages.celebrations.next_30_days') }}
            </a>
        @endif
        <a href="{{ route('church.celebrations.index', ['year' => $selectedYear, 'period' => 'all']) }}"
            class="btn btn-sm {{ ($period ?? '') === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">
            {{ __('pages.celebrations.rest_of_year', ['year' => $selectedYear]) }}
        </a>
    </div>

    <h5 class="mb-2">{{ __('pages.celebrations.browse_by_month', ['year' => $selectedYear]) }}</h5>
    <p class="text-muted small mb-2">{{ __('pages.celebrations.browse_hint') }}</p>
    <div class="d-flex flex-wrap gap-1 mb-2">
        @foreach($monthOptions as $num => $name)
            @php
                $isActive = ($period ?? '') === 'month'
                    && (int) ($selectedMonth ?? 0) === (int) $num
                    && empty($filters['from']);
            @endphp
            <a href="{{ route('church.celebrations.index', ['year' => $selectedYear, 'period' => 'month', 'month' => $num]) }}"
                class="btn btn-sm mb-1 {{ $isActive ? 'btn-success' : 'btn-outline-secondary' }}">
                {{ $name }}
            </a>
        @endforeach
    </div>

    <form method="GET" class="form-row align-items-end">
        <input type="hidden" name="period" value="month">
        <input type="hidden" name="year" value="{{ $selectedYear }}">
        <div class="form-group col-md-4 mb-2">
            <label class="small mb-1">{{ __('pages.celebrations.month_in_year', ['year' => $selectedYear]) }}</label>
            <select name="month" class="form-control form-control-sm">
                @foreach($monthOptions as $num => $name)
                    <option value="{{ $num }}" @selected((int) ($selectedMonth ?? $displayMonth) === (int) $num)>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-2 mb-2">
            <button type="submit" class="btn btn-sm btn-success btn-block">{{ __('common.view') }}</button>
        </div>
    </form>
</div>

<div class="row mb-3">
    <div class="col-md-9">
        <form method="GET" class="form-inline flex-wrap">
            <input type="hidden" name="year" value="{{ $selectedYear }}">
            @if(!empty($filters['period']))
                <input type="hidden" name="period" value="{{ $filters['period'] }}">
            @endif
            @if(!empty($filters['month']))
                <input type="hidden" name="month" value="{{ $filters['month'] }}">
            @endif
            <input type="text" name="search" class="form-control mr-2 mb-2" placeholder="{{ __('pages.celebrations.search_placeholder') }}"
                value="{{ $filters['search'] ?? '' }}">
            <select name="celebration_type" class="form-control mr-2 mb-2">
                <option value="">{{ __('pages.shared.all_types') }}</option>
                @foreach($types as $type)
                    <option value="{{ $type->value }}" @selected(($filters['celebration_type'] ?? '') === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
            <select name="source" class="form-control mr-2 mb-2">
                <option value="">{{ __('pages.shared.all_sources') }}</option>
                @foreach($sources as $source)
                    <option value="{{ $source->value }}" @selected(($filters['source'] ?? '') === $source->value)>{{ $source->label() }}</option>
                @endforeach
            </select>
            <select name="status" class="form-control mr-2 mb-2">
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? 'upcoming') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <input type="date" name="from" class="form-control mr-2 mb-2" value="{{ $filters['from'] ?? '' }}">
            <input type="date" name="to" class="form-control mr-2 mb-2" value="{{ $filters['to'] ?? '' }}">
            <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-search"></i> {{ __('common.filter') }}</button>
            <a href="{{ route('church.celebrations.index', ['year' => $selectedYear]) }}" class="btn btn-light mb-2 ml-1">{{ __('common.reset') }}</a>
        </form>
    </div>
    <div class="col-md-3 text-md-right">
        @can('create', \App\Models\Celebration::class)
            <a href="{{ route('church.celebrations.create') }}" class="btn btn-primary mb-2">
                <i class="fa fa-plus"></i> {{ __('pages.celebrations.add_celebration') }}
            </a>
        @endcan
        <form method="POST" action="{{ route('church.celebrations.sync') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-secondary mb-2" title="{{ __('pages.celebrations.sync_profiles') }}">
                <i class="fa fa-refresh"></i> {{ __('common.sync') }}
            </button>
        </form>
    </div>
</div>

<div class="tile">
    <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th>{{ __('common.date') }}</th>
                    <th>{{ __('pages.shared.in_col') }}</th>
                    <th>{{ __('pages.shared.celebration') }}</th>
                    <th>{{ __('common.member') }}</th>
                    <th>{{ __('common.type') }}</th>
                    <th>{{ __('pages.shared.source') }}</th>
                    <th>{{ __('common.status') }}</th>
                    <th width="80"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($celebrations as $item)
                    @php $daysUntil = $item->daysUntil(); @endphp
                    <tr class="{{ $daysUntil !== null && $daysUntil <= 7 ? 'table-warning' : '' }}">
                        <td nowrap>
                            {{ $item->celebration_date->format('M d, Y') }}
                            @if($item->original_date)
                                <br><small class="text-muted">{{ __('pages.celebrations.born_wed', ['date' => $item->original_date->format('M d')]) }}</small>
                            @endif
                            @if($item->yearsCount())
                                <br><small class="text-muted">{{ $item->yearsCount() }} {{ $item->celebration_type === \App\Enums\CelebrationType::Birthday ? __('pages.celebrations.years') : __('pages.celebrations.years_married') }}</small>
                            @endif
                        </td>
                        <td nowrap>
                            @if($daysUntil === 0)
                                <span class="badge badge-danger">{{ __('common.today') }}</span>
                            @elseif($daysUntil === 1)
                                <span class="badge badge-warning">{{ __('pages.shared.tomorrow') }}</span>
                            @elseif($daysUntil !== null && $daysUntil <= 30)
                                <span class="badge badge-info">{{ $daysUntil }} {{ __('pages.shared.days') }}</span>
                            @elseif($daysUntil !== null)
                                <span class="text-muted">{{ $daysUntil }} {{ __('pages.shared.days') }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $item->title }}</td>
                        <td>
                            @if($item->member)
                                <a href="{{ route('church.members.show', $item->member) }}">{{ $item->member->full_name }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td><span class="badge badge-{{ $item->celebration_type->badgeClass() }}">{{ $item->celebration_type->label() }}</span></td>
                        <td><span class="badge badge-light">{{ $item->source->label() }}</span></td>
                        <td><span class="badge badge-{{ $item->status->badgeClass() }}">{{ $item->status->label() }}</span></td>
                        <td>
                            <a href="{{ route('church.celebrations.show', $item) }}" class="btn btn-sm btn-info" title="{{ __('common.view') }}"><i class="fa fa-eye"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-muted text-center py-4">
                            {{ __('pages.celebrations.empty', ['period' => $periodLabel]) }}
                            @if($isCurrentYear)
                                {{ __('pages.celebrations.past_reappear_hint') }}
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($celebrations->hasPages())
        <div class="mt-3">{{ $celebrations->links() }}</div>
    @endif
</div>
@endsection
