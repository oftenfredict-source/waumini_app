@extends('layouts.owner')

@section('title', 'Subscription Packages')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-tags"></i> Packages</h1>
        <p>Subscription plans and features</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('owner.subscriptions.index') }}">Subscriptions</a></li>
        <li class="breadcrumb-item">Packages</li>
    </ul>
</div>

<div class="row">
    @foreach($packages as $package)
        <div class="col-md-4 mb-3">
            <div class="tile">
                <h3 class="tile-title">{{ $package->name }}</h3>
                <p>{{ $package->description }}</p>
                <hr>
                <p><strong>Installation:</strong> ${{ number_format($package->installation_price, 2) }}</p>
                <p><strong>Yearly:</strong> ${{ number_format($package->yearly_price, 2) }}</p>
                <p><strong>Trial:</strong> {{ $package->trial_days }} days</p>
                @if($package->max_members)
                    <p><strong>Max members:</strong> {{ number_format($package->max_members) }}</p>
                @endif
                <h5 class="mt-3">Features</h5>
                <ul class="mb-0">
                    @foreach($package->features as $feature)
                        <li>
                            @if($feature->pivot->is_enabled)
                                <i class="fa fa-check text-success"></i>
                            @else
                                <i class="fa fa-times text-muted"></i>
                            @endif
                            {{ $feature->name }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endforeach
</div>
@endsection
