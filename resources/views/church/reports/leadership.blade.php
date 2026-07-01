@extends('layouts.church')

@section('title', __('reports.leadership_report_title'))

@include('church.reports.partials.styles')

@section('content')
@include('church.reports.partials.nav')

<div class="report-hero">
    <h2><i class="fa fa-star"></i> {{ __('reports.leadership_report_title') }}</h2>
    <p class="lead">{{ $church->name }} — {{ __('reports.active_of_leaders', ['active' => number_format($report['active']), 'total' => number_format($report['total'])]) }}</p>
</div>

<div class="tile">
    @if($report['leaders']->isNotEmpty())
        <table class="table table-sm table-hover mb-0">
            <thead>
                <tr>
                    <th>{{ __('common.name') }}</th>
                    <th>{{ __('common.position') }}</th>
                    <th>{{ __('common.phone') }}</th>
                    <th>{{ __('reports.appointed') }}</th>
                    <th>{{ __('reports.end_date') }}</th>
                    <th>{{ __('common.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['leaders'] as $leader)
                    <tr>
                        <td>{{ $leader['name'] }}</td>
                        <td>{{ $leader['position'] }}</td>
                        <td>{{ $leader['phone'] }}</td>
                        <td>{{ $leader['appointment_date'] }}</td>
                        <td>{{ $leader['end_date'] }}</td>
                        <td>
                            <span class="badge badge-{{ $leader['is_active'] ? 'success' : 'secondary' }}">
                                {{ $leader['is_active'] ? __('common.active') : __('common.inactive') }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-muted mb-0">{{ __('reports.no_leadership') }}</p>
    @endif
</div>
@endsection
