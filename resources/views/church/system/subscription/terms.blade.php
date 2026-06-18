@extends('layouts.church')

@section('title', 'Terms & Conditions')

@section('content')
@include('church.system.partials.nav')

<div class="app-title">
    <div>
        <h1><i class="fa fa-file-text-o"></i> Terms &amp; Conditions</h1>
        <p>Platform terms governing use of {{ config('app.name') }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">Terms &amp; Conditions</li>
    </ul>
</div>

<div class="tile">
    @if($updatedAt)
        <p class="text-muted mb-3">
            <i class="fa fa-clock-o"></i> Last updated {{ \Illuminate\Support\Carbon::parse($updatedAt)->format('M d, Y H:i') }}
        </p>
    @endif

    <div class="legal-content">
        {!! $termsHtml !!}
    </div>

    <div class="mt-4 pt-3 border-top">
        @can('system.settings')
            <a href="{{ route('church.system.subscription.index') }}" class="btn btn-primary">
                <i class="fa fa-level-up"></i> Upgrade Plan
            </a>
        @endcan
        <a href="{{ route('church.dashboard') }}" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>
@endsection

@push('styles')
<style>
    .legal-content h4 {
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
        font-size: 1.05rem;
        font-weight: 700;
        color: #2a2c36;
    }
    .legal-content h4:first-child {
        margin-top: 0;
    }
    .legal-content p {
        color: #5c6873;
        line-height: 1.7;
        margin-bottom: 0.75rem;
    }
</style>
@endpush
