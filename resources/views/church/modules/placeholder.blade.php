@extends('layouts.church')

@section('title', __('pages.module_placeholder.coming_soon'))

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-wrench"></i> {{ $title }}</h1>
        <p>{{ __('pages.module_placeholder.message') }}</p>
    </div>
</div>

<div class="tile">
    <div class="text-center py-5">
        <i class="fa fa-cogs fa-4x text-muted mb-3"></i>
        <h4>{{ $title }}</h4>
        <p class="text-muted mb-4">{{ __('pages.module_placeholder.building') }}</p>
        <a href="{{ route('church.dashboard') }}" class="btn btn-primary">
            <i class="fa fa-dashboard"></i> {{ __('pages.module_placeholder.back_dashboard') }}
        </a>
    </div>
</div>
@endsection
