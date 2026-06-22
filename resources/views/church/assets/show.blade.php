@extends('layouts.church')

@section('title', $asset->name)

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-cube"></i> {{ $asset->name }}</h1>
        <p>Asset tag: <code>{{ $asset->asset_tag }}</code></p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.assets.index') }}">Assets</a></li>
        <li class="breadcrumb-item">{{ $asset->name }}</li>
    </ul>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="tile mb-3">
            <h3 class="tile-title">Asset Details</h3>
            <table class="table table-borderless mb-0">
                <tr><th width="180">Asset Tag</th><td><code>{{ $asset->asset_tag }}</code></td></tr>
                <tr><th>Name</th><td>{{ $asset->name }}</td></tr>
                <tr><th>Quantity</th><td>{{ $asset->quantity }}</td></tr>
                @if($asset->batch_id)
                    <tr><th>Registration</th><td>Part of a bulk registration (one tag per item)</td></tr>
                @endif
                <tr><th>Category</th><td>{{ $asset->category->label() }}</td></tr>
                <tr><th>Status</th>
                    <td><span class="badge badge-{{ $asset->status->badgeClass() }}">{{ $asset->status->label() }}</span></td>
                </tr>
                <tr><th>Condition</th><td>{{ $asset->condition->label() }}</td></tr>
                <tr><th>Serial Number</th><td>{{ $asset->serial_number ?? '—' }}</td></tr>
                <tr><th>Location</th><td>{{ $asset->location ?? '—' }}</td></tr>
                @if($asset->branch)
                    <tr><th>Branch</th><td>{{ $asset->branch->displayLabel() }}</td></tr>
                @endif
                <tr><th>Custodian</th>
                    <td>
                        @if($asset->custodian)
                            <a href="{{ route('church.members.show', $asset->custodian) }}">{{ $asset->custodian->full_name }}</a>
                        @else
                            —
                        @endif
                    </td>
                </tr>
                <tr><th>Purchase Date</th><td>{{ $asset->purchase_date?->format('M d, Y') ?? '—' }}</td></tr>
                <tr><th>Purchase Value</th><td>{{ $asset->purchase_value ? number_format($asset->purchase_value, 0).' TZS' : '—' }}</td></tr>
                <tr><th>Current Value</th><td>{{ $asset->current_value ? number_format($asset->current_value, 0).' TZS' : '—' }}</td></tr>
                @if($asset->disposed_at)
                    <tr><th>Disposed Date</th><td>{{ $asset->disposed_at->format('M d, Y') }}</td></tr>
                @endif
                <tr><th>Description</th><td>{{ $asset->description ?? '—' }}</td></tr>
                <tr><th>Notes</th><td>{{ $asset->notes ?? '—' }}</td></tr>
                <tr><th>Recorded By</th><td>{{ $asset->recorder?->name ?? '—' }}</td></tr>
                <tr><th>Recorded On</th><td>{{ $asset->created_at->format('M d, Y H:i') }}</td></tr>
            </table>
        </div>
    </div>

    <div class="col-lg-4">
        @if($asset->photoUrl())
            <div class="tile mb-3">
                <h3 class="tile-title">Photo</h3>
                <img src="{{ $asset->photoUrl() }}" alt="{{ $asset->name }}" class="img-fluid rounded">
            </div>
        @endif

        <div class="tile">
            <h3 class="tile-title">Actions</h3>
            <a href="{{ route('church.assets.index') }}" class="btn btn-secondary btn-block">
                <i class="fa fa-arrow-left"></i> Back to Assets
            </a>
            @can('update', $asset)
                <a href="{{ route('church.assets.edit', $asset) }}" class="btn btn-primary btn-block mt-2">
                    <i class="fa fa-pencil"></i> Edit Asset
                </a>
            @endcan
        </div>
    </div>
</div>
@endsection
