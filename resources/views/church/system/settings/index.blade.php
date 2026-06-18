@extends('layouts.church')

@section('title', 'System Settings')

@section('content')
@include('church.system.partials.nav')

<div class="app-title">
    <div>
        <h1><i class="fa fa-cog"></i> System Settings</h1>
        <p>Configure {{ $church->name }} preferences and workflows</p>
    </div>
</div>


<div class="tile">
    <ul class="nav nav-tabs" role="tablist">
        @foreach($categories as $key => $category)
            <li class="nav-item">
                <a class="nav-link @if($tab === $key) active @endif" href="{{ route('church.system.settings.index', ['tab' => $key]) }}">
                    <i class="fa {{ $category['icon'] }}"></i> {{ $category['name'] }}
                </a>
            </li>
        @endforeach
    </ul>

    <div class="p-4">
        <p class="text-muted mb-4">{{ $categories[$tab]['description'] ?? '' }}</p>

        @if($tab === 'general')
            @include('church.system.settings.tabs.general')
        @elseif($tab === 'membership')
            @include('church.system.settings.tabs.membership')
        @elseif($tab === 'finance')
            @include('church.system.settings.tabs.finance')
        @elseif($tab === 'notifications')
            @include('church.system.settings.tabs.notifications')
        @elseif($tab === 'security')
            @include('church.system.settings.tabs.security')
        @endif
    </div>
</div>
@endsection
