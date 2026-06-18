@extends('layouts.church')

@section('title', 'Church Leaders')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-star"></i> Church Leaders</h1>
        <p>Active leadership at {{ $church->name }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.member.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">Leaders</li>
    </ul>
</div>

<div class="tile">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Member ID</th>
                    <th>Appointed</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaders as $leader)
                    <tr>
                        <td>{{ $leader->member?->full_name ?? '—' }}</td>
                        <td>{{ $leader->positionLabel() }}</td>
                        <td><code>{{ $leader->member?->member_number ?? '—' }}</code></td>
                        <td>{{ $leader->appointment_date?->format('M d, Y') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted">No active leaders listed.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
