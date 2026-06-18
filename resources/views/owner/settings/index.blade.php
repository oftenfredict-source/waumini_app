@extends('layouts.owner')

@section('title', 'Settings')

@php $activeTab = request('tab', 'general'); @endphp

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-cog"></i> Platform Settings</h1>
        <p>Manage subscriptions, pricing, churches, and system configuration</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('owner.dashboard') }}">Overview</a></li>
        <li class="breadcrumb-item">Settings</li>
    </ul>
</div>

<div class="tile">
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link @if($activeTab === 'general') active @endif" href="{{ route('owner.settings.index', ['tab' => 'general']) }}">
                <i class="fa fa-info-circle"></i> General
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link @if($activeTab === 'billing') active @endif" href="{{ route('owner.settings.index', ['tab' => 'billing']) }}">
                <i class="fa fa-money"></i> Billing
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link @if($activeTab === 'packages') active @endif" href="{{ route('owner.settings.index', ['tab' => 'packages']) }}">
                <i class="fa fa-tags"></i> Packages & Pricing
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link @if($activeTab === 'churches') active @endif" href="{{ route('owner.settings.index', ['tab' => 'churches']) }}">
                <i class="fa fa-building"></i> Churches
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link @if($activeTab === 'notifications') active @endif" href="{{ route('owner.settings.index', ['tab' => 'notifications']) }}">
                <i class="fa fa-bell"></i> Notifications
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link @if($activeTab === 'legal') active @endif" href="{{ route('owner.settings.index', ['tab' => 'legal']) }}">
                <i class="fa fa-file-text-o"></i> Legal
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link @if($activeTab === 'system') active @endif" href="{{ route('owner.settings.index', ['tab' => 'system']) }}">
                <i class="fa fa-server"></i> System
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
