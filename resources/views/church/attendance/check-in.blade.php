@extends('layouts.church')

@section('title', $title)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <div class="tile text-center p-4">
            <div class="mb-3">
                <i class="fa fa-{{ $ok ? ($already ? 'info-circle' : 'check-circle') : 'exclamation-circle' }} fa-4x"
                   style="color: {{ $ok ? ($already ? '#17a2b8' : '#28a745') : '#dc3545' }};"></i>
            </div>
            <h2 class="mb-2">{{ $message }}</h2>
            @if($serviceTitle)
                <p class="text-muted mb-4">{{ $serviceTitle }}</p>
            @endif
            @if(auth()->user()?->canAccessMemberPortal())
                <a href="{{ route('church.member.dashboard') }}" class="btn btn-primary">
                    {{ __('common.dashboard') }}
                </a>
            @else
                <a href="{{ route('church.dashboard') }}" class="btn btn-primary">
                    {{ __('common.dashboard') }}
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
