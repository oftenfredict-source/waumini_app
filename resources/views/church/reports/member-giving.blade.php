@extends('layouts.church')

@section('title', 'Member Giving Report')

@include('church.reports.partials.styles')

@section('content')
@php $currency = $report['currency']; @endphp

@include('church.reports.partials.nav')

<div class="report-hero">
    <h2><i class="fa fa-money"></i> Member Giving Report</h2>
    <p class="lead">{{ $start->format('M d, Y') }} – {{ $end->format('M d, Y') }}</p>
</div>

@include('church.reports.partials.date-filter')

<div class="tile mb-3 no-print">
    <form method="GET" class="form-inline">
        <input type="hidden" name="start_date" value="{{ $start->format('Y-m-d') }}">
        <input type="hidden" name="end_date" value="{{ $end->format('Y-m-d') }}">
        <label class="mr-2">Filter by member:</label>
        <select name="member_id" class="form-control mr-2" onchange="this.form.submit()">
            <option value="">All members</option>
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
                <div class="report-stat-label">Member</div>
                <div class="report-stat-value" style="font-size:1.1rem;">{{ $report['member']->full_name }}</div>
                <small class="text-muted">{{ $report['member']->member_number }}</small>
            </div></div>
        </div>
        <div class="col-md-6">
            <div class="card report-stat-card"><div class="card-body">
                <div class="report-stat-label">Total Giving</div>
                <div class="report-stat-value text-success">{{ $currency }} {{ number_format($report['total'], 2) }}</div>
            </div></div>
        </div>
    </div>

    <div class="tile">
        <h3 class="tile-title">Transaction Details</h3>
        @if($report['transactions']->isNotEmpty())
            <table class="table table-sm table-hover mb-0">
                <thead><tr><th>Date</th><th>Type</th><th class="text-right">Amount ({{ $currency }})</th></tr></thead>
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
            <p class="text-muted mb-0">No transactions for this member in the selected period.</p>
        @endif
    </div>
@else
    <div class="tile">
        <h3 class="tile-title">All Contributors — Total: {{ $currency }} {{ number_format($report['total'], 2) }}</h3>
        @if($report['contributors']->isNotEmpty())
            <table class="table table-sm table-hover mb-0">
                <thead><tr><th>#</th><th>Member ID</th><th>Name</th><th class="text-right">Total ({{ $currency }})</th></tr></thead>
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
            <p class="text-muted mb-0">No member giving recorded for this period.</p>
        @endif
    </div>
@endif
@endsection
