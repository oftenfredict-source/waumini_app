@extends('layouts.church')

@section('title', 'Budget Performance')

@include('church.reports.partials.styles')

@section('content')
@php $currency = $report['currency']; @endphp

@include('church.reports.partials.nav')

<div class="report-hero">
    <h2><i class="fa fa-pie-chart"></i> Budget Performance</h2>
    <p class="lead">Budget utilization for {{ $church->name }}</p>
</div>

<div class="tile">
    @if($report['budgets']->isNotEmpty())
        <table class="table table-sm table-hover mb-0">
            <thead>
                <tr>
                    <th>Budget</th>
                    <th>Year</th>
                    <th>Status</th>
                    <th class="text-right">Budget</th>
                    <th class="text-right">Spent</th>
                    <th class="text-right">Remaining</th>
                    <th class="text-right">Utilization</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['budgets'] as $row)
                    <tr>
                        <td>{{ $row['name'] }}</td>
                        <td>{{ $row['fiscal_year'] }}</td>
                        <td>{{ $row['status'] }}</td>
                        <td class="text-right">{{ number_format($row['total_budget'], 2) }}</td>
                        <td class="text-right">{{ number_format($row['spent'], 2) }}</td>
                        <td class="text-right">{{ number_format($row['remaining'], 2) }}</td>
                        <td class="text-right">
                            <span class="badge badge-{{ $row['utilization'] > 90 ? 'danger' : ($row['utilization'] > 70 ? 'warning' : 'success') }}">
                                {{ $row['utilization'] }}%
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-muted mb-0">No budgets have been created yet.</p>
    @endif
</div>
@endsection
