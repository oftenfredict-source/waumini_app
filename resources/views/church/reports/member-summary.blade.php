@extends('layouts.church')

@section('title', __('reports.member_summary_report'))

@include('church.reports.partials.styles')

@section('content')
@include('church.reports.partials.nav')

<div class="report-hero">
    <h2><i class="fa fa-users"></i> {{ __('reports.member_summary_report') }}</h2>
    <p class="lead">{{ $church->name }} — {{ __('reports.as_of') }} {{ now()->format('M d, Y') }}</p>
</div>

<div class="row mb-3">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card report-stat-card"><div class="card-body">
            <div class="report-stat-label">{{ __('reports.total_members') }}</div>
            <div class="report-stat-value">{{ number_format($report['total']) }}</div>
        </div></div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card report-stat-card"><div class="card-body">
            <div class="report-stat-label">{{ __('reports.children') }}</div>
            <div class="report-stat-value">{{ number_format($report['children']) }}</div>
        </div></div>
    </div>
</div>

<div class="row">
    @foreach([
        'by_status' => __('reports.by_status'),
        'by_gender' => __('reports.by_gender'),
        'by_member_type' => __('reports.by_member_type'),
        'by_membership_type' => __('reports.by_membership_type'),
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
                    <p class="text-muted mb-0">{{ __('reports.no_data') }}</p>
                @endif
            </div>
        </div>
    @endforeach
</div>

<div class="tile">
    <h3 class="tile-title">{{ __('reports.registrations_12_months') }}</h3>
    <table class="table table-sm table-hover mb-0">
        <thead><tr><th>{{ __('reports.month') }}</th><th class="text-right">{{ __('reports.new_members') }}</th></tr></thead>
        <tbody>
            @foreach($report['registrations'] as $row)
                <tr><td>{{ $row['month'] }}</td><td class="text-right">{{ $row['count'] }}</td></tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
