@extends('layouts.church')

@section('title', 'Member Summary Report')

@include('church.reports.partials.styles')

@section('content')
@include('church.reports.partials.nav')

<div class="report-hero">
    <h2><i class="fa fa-users"></i> Member Summary Report</h2>
    <p class="lead">{{ $church->name }} — as of {{ now()->format('M d, Y') }}</p>
</div>

<div class="row mb-3">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card report-stat-card"><div class="card-body">
            <div class="report-stat-label">Total Members</div>
            <div class="report-stat-value">{{ number_format($report['total']) }}</div>
        </div></div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card report-stat-card"><div class="card-body">
            <div class="report-stat-label">Children</div>
            <div class="report-stat-value">{{ number_format($report['children']) }}</div>
        </div></div>
    </div>
</div>

<div class="row">
    @foreach([
        'by_status' => 'By Status',
        'by_gender' => 'By Gender',
        'by_member_type' => 'By Member Type',
        'by_membership_type' => 'By Membership Type',
    ] as $key => $title)
        <div class="col-lg-6 mb-4">
            <div class="tile">
                <h3 class="tile-title">{{ $title }}</h3>
                @if(!empty($report[$key]))
                    <table class="table table-sm mb-0">
                        @foreach($report[$key] as $label => $count)
                            <tr><td>{{ $label }}</td><td class="text-right font-weight-bold">{{ $count }}</td></tr>
                        @endforeach
                    </table>
                @else
                    <p class="text-muted mb-0">No data available.</p>
                @endif
            </div>
        </div>
    @endforeach
</div>

<div class="tile">
    <h3 class="tile-title">Registrations (Last 12 Months)</h3>
    <table class="table table-sm table-hover mb-0">
        <thead><tr><th>Month</th><th class="text-right">New Members</th></tr></thead>
        <tbody>
            @foreach($report['registrations'] as $row)
                <tr><td>{{ $row['month'] }}</td><td class="text-right">{{ $row['count'] }}</td></tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
