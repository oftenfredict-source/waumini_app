@extends('layouts.church')

@section('title', 'Leadership Report')

@include('church.reports.partials.styles')

@section('content')
@include('church.reports.partials.nav')

<div class="report-hero">
    <h2><i class="fa fa-star"></i> Leadership Report</h2>
    <p class="lead">{{ $church->name }} — {{ number_format($report['active']) }} active of {{ number_format($report['total']) }} leaders</p>
</div>

<div class="tile">
    @if($report['leaders']->isNotEmpty())
        <table class="table table-sm table-hover mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Phone</th>
                    <th>Appointed</th>
                    <th>End Date</th>
                    <th>Status</th>
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
                                {{ $leader['is_active'] ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-muted mb-0">No leadership records found.</p>
    @endif
</div>
@endsection
