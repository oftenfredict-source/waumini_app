@extends('layouts.church')

@section('title', __('reports.budget_performance'))

@include('church.reports.partials.styles')

@section('content')
@php $currency = $report['currency']; @endphp

@include('church.reports.partials.nav')

<div class="report-hero">
    <h2><i class="fa fa-pie-chart"></i> {{ __('reports.budget_performance') }}</h2>
    <p class="lead">{{ __('reports.budget_utilization_for', ['church' => $church->name]) }}</p>
</div>

<div class="tile">
    @if($report['budgets']->isNotEmpty())
        <table class="table table-sm table-hover mb-0">
            <thead>
                <tr>
                    <th>{{ __('reports.budget_label') }}</th>
                    <th>{{ __('reports.year') }}</th>
                    <th>{{ __('common.status') }}</th>
                    <th class="text-right">{{ __('reports.budget_label') }}</th>
                    <th class="text-right">{{ __('reports.spent') }}</th>
                    <th class="text-right">{{ __('reports.remaining') }}</th>
                    <th class="text-right">{{ __('reports.utilization') }}</th>
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
        <p class="text-muted mb-0">{{ __('reports.no_budgets') }}</p>
    @endif
</div>
@endsection
