@extends('layouts.owner')

@section('title', __('owner.subs.packages_title'))

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-tags"></i> {{ __('owner.packages') }}</h1>
        <p>{{ __('owner.subs.packages_subtitle') }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('owner.subscriptions.index') }}">{{ __('owner.subscriptions') }}</a></li>
        <li class="breadcrumb-item">{{ __('owner.packages') }}</li>
    </ul>
</div>

<div class="row">
    @foreach($packages as $package)
        <div class="col-md-4 mb-3">
            <div class="tile">
                <h3 class="tile-title">{{ $package->name }}</h3>
                <p>{{ $package->description }}</p>
                <hr>
                <p><strong>{{ __('owner.subs.installation') }}</strong> ${{ number_format($package->installation_price, 2) }}</p>
                <p><strong>{{ __('owner.subs.yearly') }}</strong> ${{ number_format($package->yearly_price, 2) }}</p>
                <p><strong>{{ __('owner.subs.trial_days', ['days' => $package->trial_days]) }}</strong></p>
                @if($package->max_members)
                    <p><strong>{{ __('owner.subs.max_members') }}</strong> {{ number_format($package->max_members) }}</p>
                @endif
                <h5 class="mt-3">{{ __('owner.subs.features') }}</h5>
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
