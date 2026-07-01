@extends('layouts.owner')

@section('title', __('owner.church.title'))

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-building"></i> {{ __('owner.church.title') }}</h1>
        <p>{{ __('owner.church.subtitle') }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('owner.dashboard') }}">{{ __('owner.overview') }}</a></li>
        <li class="breadcrumb-item">{{ __('owner.churches') }}</li>
    </ul>
</div>

<div class="tile">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <form method="GET" class="form-inline">
            <input type="text" name="search" class="form-control mr-2 mb-2" placeholder="{{ __('owner.church.search_placeholder') }}" value="{{ $filters['search'] ?? '' }}">
            <select name="status" class="form-control mr-2 mb-2">
                <option value="">{{ __('pages.shared.all_statuses') }}</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-search"></i> {{ __('common.filter') }}</button>
        </form>
        @can('create', App\Models\Church::class)
            <a href="{{ route('owner.churches.create') }}" class="btn btn-primary mb-2">
                <i class="fa fa-plus"></i> {{ __('owner.church.add') }}
            </a>
        @endcan
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th>{{ __('owner.church_label') }}</th>
                    <th>{{ __('owner.church.subdomain') }}</th>
                    <th>{{ __('owner.email') }}</th>
                    <th>{{ __('owner.package') }}</th>
                    <th>{{ __('owner.status') }}</th>
                    <th>{{ __('owner.church.created') }}</th>
                    <th width="180">{{ __('common.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($churches as $church)
                    <tr>
                        <td>
                            <strong>{{ $church->name }}</strong><br>
                            <small class="text-muted">{{ $church->pastor_name }}</small>
                        </td>
                        <td><code>{{ $church->slug }}</code></td>
                        <td>{{ $church->email }}</td>
                        <td>{{ $church->activeSubscription?->package?->name ?? '—' }}</td>
                        <td>@include('owner.components.status-badge', ['status' => $church->status])</td>
                        <td>{{ $church->created_at->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('owner.churches.show', $church) }}" class="btn btn-sm btn-info" title="{{ __('common.view') }}"><i class="fa fa-eye"></i></a>
                            @can('impersonate', $church)
                                @if($church->adminUser)
                                    <form method="POST" action="{{ route('owner.churches.impersonate', $church) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary" title="{{ __('owner.church.enter_church') }}">
                                            <i class="fa fa-sign-in"></i>
                                        </button>
                                    </form>
                                @endif
                            @endcan
                            <a href="{{ route('owner.churches.edit', $church) }}" class="btn btn-sm btn-warning" title="{{ __('common.edit') }}"><i class="fa fa-edit"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">{{ __('owner.church.no_found') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $churches->links() }}
</div>
@endsection
