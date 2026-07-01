@extends('layouts.church')

@section('title', __('reports.member_giving_report'))

@include('church.reports.partials.styles')

@section('content')
@php $currency = $report['currency']; @endphp

@include('church.reports.partials.nav')

<div class="report-hero">
    <h2><i class="fa fa-money"></i> {{ __('reports.member_giving_report') }}</h2>
    <p class="lead">{{ $start->format('M d, Y') }} – {{ $end->format('M d, Y') }}</p>
</div>

@include('church.reports.partials.date-filter')

<div class="tile mb-3 no-print">
    <form method="GET" class="form-inline">
        <input type="hidden" name="start_date" value="{{ $start->format('Y-m-d') }}">
        <input type="hidden" name="end_date" value="{{ $end->format('Y-m-d') }}">
        <label class="mr-2">{{ __('reports.filter_by_member') }}</label>
        <select name="member_id" class="form-control mr-2" onchange="this.form.submit()">
            <option value="">{{ __('reports.all_members') }}</option>
            @foreach($report['members'] as $member)
                <option value="{{ $member->id }}" @selected(optional($report['member'])->id === $member->id)>
                    {{ $member->full_name }} ({{ $member->member_number }})
                </option>
            @endforeach
        </select>
    </form>
</div>

@if($report['member'])
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card report-stat-card"><div class="card-body">
                <div class="report-stat-label">{{ __('common.member') }}</div>
                <div class="report-stat-value" style="font-size:1.1rem;">{{ $report['member']->full_name }}</div>
                <small class="text-muted">{{ $report['member']->member_number }}</small>
            </div></div>
        </div>
        <div class="col-md-6">
            <div class="card report-stat-card"><div class="card-body">
                <div class="report-stat-label">{{ __('reports.total_giving') }}</div>
                <div class="report-stat-value text-success">{{ $currency }} {{ number_format($report['total'], 2) }}</div>
            </div></div>
        </div>
    </div>

    <div class="tile">
        <h3 class="tile-title">{{ __('reports.transaction_details') }}</h3>
        @if($report['transactions']->isNotEmpty())
            <table class="table table-sm table-hover mb-0">
                <thead><tr><th>{{ __('common.date') }}</th><th>{{ __('common.type') }}</th><th class="text-right">{{ __('reports.amount') }} ({{ $currency }})</th></tr></thead>
                <tbody>
                    @foreach($report['transactions'] as $tx)
                        <tr>
                            <td>{{ $tx['date']?->format('M d, Y') ?? '—' }}</td>
                            <td>{{ $tx['type'] }}</td>
                            <td class="text-right">{{ number_format($tx['amount'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-muted mb-0">{{ __('reports.no_transactions_member') }}</p>
        @endif
    </div>
@else
    <div class="tile">
        <h3 class="tile-title">{{ __('reports.all_contributors_total') }} {{ $currency }} {{ number_format($report['total'], 2) }}</h3>
        @if($report['contributors']->isNotEmpty())
            <table class="table table-sm table-hover mb-0">
                <thead><tr><th>#</th><th>{{ __('reports.member_id') }}</th><th>{{ __('common.name') }}</th><th class="text-right">{{ __('common.total') }} ({{ $currency }})</th></tr></thead>
                <tbody>
                    @foreach($report['contributors'] as $i => $row)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $row['member_number'] }}</td>
                            <td>
                                <a href="{{ route('church.reports.member-giving', ['member_id' => $row['member_id'], 'start_date' => $start->format('Y-m-d'), 'end_date' => $end->format('Y-m-d')]) }}">
                                    {{ $row['full_name'] }}
                                </a>
                            </td>
                            <td class="text-right font-weight-bold">{{ number_format($row['total'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-muted mb-0">{{ __('reports.no_member_giving') }}</p>
        @endif
    </div>
@endif
@endsection
