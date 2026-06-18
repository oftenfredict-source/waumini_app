@extends('layouts.church')

@section('title', 'Church Services')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-calendar"></i> Church Services</h1>
        <p>Upcoming services at {{ $church->name }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.member.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">Services</li>
    </ul>
</div>

<div class="tile">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Preacher</th>
                    <th>Venue</th>
                    <th>Theme</th>
                </tr>
            </thead>
            <tbody>
                @forelse($services as $service)
                    <tr>
                        <td>{{ $service->displayTitle() }}</td>
                        <td>{{ $service->service_date?->format('M d, Y') ?? '—' }}</td>
                        <td>
                            @if($service->start_time)
                                {{ \Illuminate\Support\Carbon::parse($service->start_time)->format('g:i A') }}
                                @if($service->end_time)
                                    – {{ \Illuminate\Support\Carbon::parse($service->end_time)->format('g:i A') }}
                                @endif
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $service->preacher ?? '—' }}</td>
                        <td>{{ $service->venue ?? '—' }}</td>
                        <td>{{ $service->theme ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted">No upcoming services scheduled.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
