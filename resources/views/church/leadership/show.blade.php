@extends('layouts.church')

@section('title', $leader->positionLabel())

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-star"></i> {{ $leader->positionLabel() }}</h1>
        <p>Leadership assignment details</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.leadership.index') }}">Leadership</a></li>
        <li class="breadcrumb-item">{{ $leader->member?->full_name }}</li>
    </ul>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="tile">
            <h3 class="tile-title">Assignment Details</h3>
            <table class="table table-borderless">
                <tr><th width="180">Member</th><td>{{ $leader->member?->full_name ?? '—' }}</td></tr>
                <tr><th>Member Number</th><td><code>{{ $leader->member?->member_number ?? '—' }}</code></td></tr>
                <tr><th>Position</th><td>{{ $leader->positionLabel() }}</td></tr>
                <tr><th>Appointment Date</th><td>{{ $leader->appointment_date->format('M d, Y') }}</td></tr>
                <tr><th>End Date</th><td>{{ $leader->end_date?->format('M d, Y') ?? '—' }}</td></tr>
                <tr><th>Appointed By</th><td>{{ $leader->appointed_by ?? '—' }}</td></tr>
                <tr><th>Status</th>
                    <td>
                        <span class="badge badge-{{ $leader->isCurrentlyActive() ? 'success' : 'secondary' }}">
                            {{ $leader->isCurrentlyActive() ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                </tr>
                <tr><th>Description</th><td>{{ $leader->description ?? '—' }}</td></tr>
                <tr><th>Notes</th><td>{{ $leader->notes ?? '—' }}</td></tr>
                <tr><th>Assigned On</th><td>{{ $leader->created_at->format('M d, Y H:i') }}</td></tr>
            </table>
        </div>
    </div>
    <div class="col-md-4">
        <div class="tile">
            <h3 class="tile-title">Actions</h3>
            <a href="{{ route('church.leadership.index') }}" class="btn btn-secondary btn-block mb-2">
                <i class="fa fa-arrow-left"></i> Back to Leaders
            </a>
            @if($leader->member)
                <a href="{{ route('church.members.show', $leader->member) }}" class="btn btn-outline-primary btn-block mb-2">
                    <i class="fa fa-user"></i> View Member Profile
                </a>
            @endif
            @can('deactivate', $leader)
                @if($leader->isCurrentlyActive())
                    <form method="POST" action="{{ route('church.leadership.deactivate', $leader) }}"
                        data-swal-confirm="End this leadership assignment?">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-block">
                            <i class="fa fa-ban"></i> End Assignment
                        </button>
                    </form>
                @endif
            @endcan
        </div>
    </div>
</div>
@endsection
