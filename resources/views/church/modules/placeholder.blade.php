@extends('layouts.church')

@section('title', 'Coming Soon')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-wrench"></i> {{ $title }}</h1>
        <p>This module will be available in the next implementation phase.</p>
    </div>
</div>

<div class="tile">
    <div class="text-center py-5">
        <i class="fa fa-cogs fa-4x text-muted mb-3"></i>
        <h4>{{ $title }}</h4>
        <p class="text-muted mb-4">We are building this feature for your church management system.</p>
        <a href="{{ route('church.dashboard') }}" class="btn btn-primary">
            <i class="fa fa-dashboard"></i> Back to Dashboard
        </a>
    </div>
</div>
@endsection
