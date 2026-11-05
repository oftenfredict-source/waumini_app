@extends('layouts.owner')

@section('title', __('owner.set.title'))

@php $activeTab = request('tab', 'general'); @endphp

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">{{ __('owner.set.heading') }}</h1>
        <p class="text-muted mb-0">{{ __('owner.set.subtitle') }}</p>
    </div>
</div>

<div class="card owner-panel-card owner-settings-card">
    <div class="card-header border-bottom-0 pb-0">
        <ul class="nav nav-pills owner-settings-nav flex-wrap gap-1" role="tablist">
            <li class="nav-item">
                <a class="nav-link @if($activeTab === 'general') active @endif" href="{{ route('owner.settings.index', ['tab' => 'general']) }}">
                    <i class="bi bi-info-circle me-1"></i> {{ __('owner.set.tab_general') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link @if($activeTab === 'billing') active @endif" href="{{ route('owner.settings.index', ['tab' => 'billing']) }}">
                    <i class="bi bi-cash-coin me-1"></i> {{ __('owner.set.tab_billing') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link @if($activeTab === 'packages') active @endif" href="{{ route('owner.settings.index', ['tab' => 'packages']) }}">
                    <i class="bi bi-tags me-1"></i> {{ __('owner.set.tab_packages') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link @if($activeTab === 'churches') active @endif" href="{{ route('owner.settings.index', ['tab' => 'churches']) }}">
                    <i class="bi bi-building me-1"></i> {{ __('owner.set.tab_churches') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link @if($activeTab === 'notifications') active @endif" href="{{ route('owner.settings.index', ['tab' => 'notifications']) }}">
                    <i class="bi bi-bell me-1"></i> {{ __('owner.set.tab_notifications') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link @if($activeTab === 'legal') active @endif" href="{{ route('owner.settings.index', ['tab' => 'legal']) }}">
                    <i class="bi bi-file-text me-1"></i> {{ __('owner.set.tab_legal') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link @if($activeTab === 'system') active @endif" href="{{ route('owner.settings.index', ['tab' => 'system']) }}">
                    <i class="bi bi-hdd-stack me-1"></i> {{ __('owner.set.tab_system') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link @if($activeTab === 'backup') active @endif" href="{{ route('owner.settings.index', ['tab' => 'backup']) }}">
                    <i class="bi bi-cloud-arrow-up me-1"></i> {{ __('owner.set.tab_backup') }}
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body pt-4">
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
        @elseif($activeTab === 'backup')
            @include('owner.settings.tabs.backup')
        @endif
    </div>
</div>
@endsection
