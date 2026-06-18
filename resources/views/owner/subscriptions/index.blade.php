@extends('layouts.owner')

@section('title', 'Subscriptions')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-credit-card"></i> Subscriptions</h1>
        <p>Manage packages and church subscriptions</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('owner.dashboard') }}">Overview</a></li>
        <li class="breadcrumb-item">Subscriptions</li>
    </ul>
</div>

<div class="row mb-3">
    @foreach($packages as $package)
        <div class="col-md-4">
            <div class="tile">
                <h4>{{ $package->name }}</h4>
                <p class="text-muted">{{ $package->description }}</p>
                <p><strong>Installation:</strong> {{ \App\Models\SystemSetting::platformCurrency() }} {{ number_format($package->installation_price, 2) }} &middot; <strong>Yearly:</strong> {{ \App\Models\SystemSetting::platformCurrency() }} {{ number_format($package->yearly_price, 2) }}</p>
                <p><span class="badge badge-info">{{ $package->active_count }} active</span> &middot; {{ $package->trial_days }} day trial</p>
                <a href="{{ route('owner.settings.index', ['tab' => 'packages']) }}" class="btn btn-sm btn-outline-primary">Manage in Settings</a>
            </div>
        </div>
    @endforeach
</div>

<div class="tile">
    <h3 class="tile-title">Church Subscriptions</h3>
    <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th>Church</th>
                    <th>Package</th>
                    <th>Billing</th>
                    <th>Status</th>
                    <th>Starts</th>
                    <th>Ends / Trial</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subscriptions as $subscription)
                    <tr>
                        <td>
                            <a href="{{ route('owner.churches.show', $subscription->church) }}">{{ $subscription->church?->name }}</a>
                        </td>
                        <td>{{ $subscription->package?->name }}</td>
                        <td>{{ ucfirst($subscription->billing_cycle->value) }}</td>
                        <td><span class="badge badge-secondary">{{ ucfirst($subscription->status->value) }}</span></td>
                        <td>{{ $subscription->starts_at?->format('M d, Y') }}</td>
                        <td>{{ ($subscription->trial_ends_at ?? $subscription->ends_at)?->format('M d, Y') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">No subscriptions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $subscriptions->links() }}
</div>
@endsection
