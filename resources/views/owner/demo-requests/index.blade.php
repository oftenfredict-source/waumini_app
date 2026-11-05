@extends('layouts.owner')

@section('title', __('owner.demo.title'))

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-handshake-o"></i> {{ __('owner.demo.heading') }}</h1>
        <p>{{ __('owner.demo.subtitle') }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('owner.dashboard') }}">{{ __('owner.overview') }}</a></li>
        <li class="breadcrumb-item">{{ __('owner.demo_requests') }}</li>
    </ul>
</div>

@if(session('admin_credentials'))
    <div class="alert alert-success">
        <strong>{{ __('owner.demo.credentials_ready') }}</strong><br>
        {{ __('owner.church_label') }}: {{ session('admin_credentials.church') }}<br>
        {{ __('owner.email') }}: <code>{{ session('admin_credentials.email') }}</code><br>
        {{ __('owner.demo.temp_password') }}: <code>{{ session('admin_credentials.password') }}</code>
    </div>
@endif

<div class="row">
    <div class="col-md-4">
        <div class="widget-small warning coloured-icon">
            <i class="icon fa fa-clock-o fa-3x"></i>
            <div class="info"><h4>{{ __('owner.demo.pending') }}</h4><p><b>{{ $stats['pending'] }}</b></p></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="widget-small success coloured-icon">
            <i class="icon fa fa-check fa-3x"></i>
            <div class="info"><h4>{{ __('owner.demo.approved') }}</h4><p><b>{{ $stats['approved'] }}</b></p></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="widget-small danger coloured-icon">
            <i class="icon fa fa-times fa-3x"></i>
            <div class="info"><h4>{{ __('owner.demo.rejected') }}</h4><p><b>{{ $stats['rejected'] }}</b></p></div>
        </div>
    </div>
</div>

<div class="tile">
    <form method="GET" class="form-inline mb-3">
        <select name="status" class="form-control mr-2">
            <option value="">{{ __('pages.shared.all_statuses') }}</option>
            <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>{{ __('owner.demo.pending') }}</option>
            <option value="approved" @selected(($filters['status'] ?? '') === 'approved')>{{ __('owner.demo.approved') }}</option>
            <option value="rejected" @selected(($filters['status'] ?? '') === 'rejected')>{{ __('owner.demo.rejected') }}</option>
        </select>
        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> {{ __('common.filter') }}</button>
    </form>

    <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th>{{ __('owner.church_label') }}</th>
                    <th>{{ __('owner.demo.contact') }}</th>
                    <th>{{ __('owner.package') }}</th>
                    <th>{{ __('owner.status') }}</th>
                    <th>{{ __('owner.demo.submitted') }}</th>
                    <th width="260">{{ __('common.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $demoRequest)
                    <tr>
                        <td>
                            <strong>{{ $demoRequest->church_name }}</strong>
                            @if($demoRequest->city || $demoRequest->country)
                                <br><small class="text-muted">{{ collect([$demoRequest->city, $demoRequest->country])->filter()->implode(', ') }}</small>
                            @endif
                            @if($demoRequest->message)
                                <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($demoRequest->message, 80) }}</small>
                            @endif
                        </td>
                        <td>
                            {{ $demoRequest->contact_name }}<br>
                            <small>{{ $demoRequest->email }}</small>
                            @if($demoRequest->phone)
                                <br><small>{{ $demoRequest->phone }}</small>
                            @endif
                        </td>
                        <td>{{ $demoRequest->package?->name ?? '—' }}</td>
                        <td>
                            <span class="badge badge-{{ $demoRequest->status === 'pending' ? 'warning' : ($demoRequest->status === 'approved' ? 'success' : 'danger') }}">
                                {{ __('owner.demo.'.$demoRequest->status) }}
                            </span>
                            @if($demoRequest->church)
                                <br><a href="{{ route('owner.churches.show', $demoRequest->church) }}">{{ __('owner.demo.view_church') }}</a>
                            @endif
                            @if($demoRequest->rejection_reason)
                                <br><small class="text-muted">{{ $demoRequest->rejection_reason }}</small>
                            @endif
                        </td>
                        <td>{{ $demoRequest->created_at->format('M d, Y H:i') }}</td>
                        <td>
                            @if($demoRequest->isPending())
                                @can('approve', $demoRequest)
                                    <form method="POST" action="{{ route('owner.demo-requests.approve', $demoRequest) }}" class="mb-2">
                                        @csrf
                                        <div class="form-row align-items-center">
                                            <div class="col-auto mb-1">
                                                <select name="package_id" class="form-control form-control-sm">
                                                    @foreach($packages as $package)
                                                        <option value="{{ $package->id }}" @selected((int) $demoRequest->package_id === (int) $package->id)>
                                                            {{ $package->name }} ({{ $package->trial_days }}d)
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-auto mb-1">
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm(@json(__('owner.demo.approve_confirm')))">
                                                    <i class="fa fa-check"></i> {{ __('owner.demo.approve') }}
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                @endcan
                                @can('reject', $demoRequest)
                                    <form method="POST" action="{{ route('owner.demo-requests.reject', $demoRequest) }}">
                                        @csrf
                                        <div class="input-group input-group-sm">
                                            <input type="text" name="rejection_reason" class="form-control" placeholder="{{ __('owner.demo.reject_reason') }}">
                                            <div class="input-group-append">
                                                <button type="submit" class="btn btn-danger" onclick="return confirm(@json(__('owner.demo.reject_confirm')))">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                @endcan
                            @else
                                <small class="text-muted">
                                    {{ $demoRequest->reviewer?->name ?? '—' }}
                                    @if($demoRequest->reviewed_at)
                                        <br>{{ $demoRequest->reviewed_at->format('M d, Y H:i') }}
                                    @endif
                                </small>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">{{ __('owner.demo.empty') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $requests->links() }}
</div>
@endsection
