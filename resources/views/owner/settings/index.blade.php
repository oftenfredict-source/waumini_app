@extends('layouts.owner')

@section('title', __('owner.set.title'))

@php $activeTab = request('tab', 'general'); @endphp

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-cog"></i> {{ __('owner.set.heading') }}</h1>
        <p>{{ __('owner.set.subtitle') }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('owner.dashboard') }}">{{ __('owner.overview') }}</a></li>
        <li class="breadcrumb-item">{{ __('owner.settings') }}</li>
    </ul>
</div>

<div class="tile">
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link @if($activeTab === 'general') active @endif" href="{{ route('owner.settings.index', ['tab' => 'general']) }}">
                <i class="fa fa-info-circle"></i> {{ __('owner.set.tab_general') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link @if($activeTab === 'billing') active @endif" href="{{ route('owner.settings.index', ['tab' => 'billing']) }}">
                <i class="fa fa-money"></i> {{ __('owner.set.tab_billing') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link @if($activeTab === 'packages') active @endif" href="{{ route('owner.settings.index', ['tab' => 'packages']) }}">
                <i class="fa fa-tags"></i> {{ __('owner.set.tab_packages') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link @if($activeTab === 'churches') active @endif" href="{{ route('owner.settings.index', ['tab' => 'churches']) }}">
                <i class="fa fa-building"></i> {{ __('owner.set.tab_churches') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link @if($activeTab === 'notifications') active @endif" href="{{ route('owner.settings.index', ['tab' => 'notifications']) }}">
                <i class="fa fa-bell"></i> {{ __('owner.set.tab_notifications') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link @if($activeTab === 'legal') active @endif" href="{{ route('owner.settings.index', ['tab' => 'legal']) }}">
                <i class="fa fa-file-text-o"></i> {{ __('owner.set.tab_legal') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link @if($activeTab === 'system') active @endif" href="{{ route('owner.settings.index', ['tab' => 'system']) }}">
                <i class="fa fa-server"></i> {{ __('owner.set.tab_system') }}
            </a>
        </li>
    </ul>

    <div class="p-4">
        @if($activeTab === 'general')
            @include('owner.settings.tabs.general')
        @elseif($activeTab === 'billing')
            @include('owner.settings.tabs.billing')
        @elseif($activeTab === 'packages')
            @include('owner.settings.tabs.packages')
        @elseif($activeTab === 'churches')
            @include('owner.settings.tabs.churches')
        @elseif($activeTab === 'notifications')
            @include('owner.settings.tabs.notifications')
        @elseif($activeTab === 'legal')
            @include('owner.settings.tabs.legal')
        @elseif($activeTab === 'system')
            @include('owner.settings.tabs.system')
        @endif
    </div>
</div>
@endsection
