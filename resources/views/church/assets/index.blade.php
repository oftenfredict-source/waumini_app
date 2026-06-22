@extends('layouts.church')

@section('title', 'Church Assets')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-cubes"></i> Church Assets</h1>
        <p>Record and manage church property and equipment</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">Assets</li>
    </ul>
</div>

<div class="row mb-3">
    <div class="col-md-8">
        <form method="GET" class="form-inline">
            <input type="text" name="search" class="form-control mr-2 mb-2" placeholder="Search asset..."
                value="{{ $filters['search'] ?? '' }}">
            <select name="category" class="form-control mr-2 mb-2">
                <option value="">All categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->value }}" @selected(($filters['category'] ?? '') === $category->value)>{{ $category->label() }}</option>
                @endforeach
            </select>
            <select name="status" class="form-control mr-2 mb-2">
                <option value="">All statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            @if($canFilterBranches ?? false)
                <select name="branch_id" class="form-control mr-2 mb-2">
                    <option value="">All branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) ($filters['branch_id'] ?? '') === (string) $branch->id)>{{ $branch->displayLabel() }}</option>
                    @endforeach
                </select>
            @endif
            <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-search"></i> Filter</button>
        </form>
    </div>
    <div class="col-md-4 text-md-right">
        @can('create', \App\Models\ChurchAsset::class)
            <a href="{{ route('church.assets.create') }}" class="btn btn-primary mb-2">
                <i class="fa fa-plus"></i> Record Asset
            </a>
        @endcan
    </div>
</div>

<div class="tile">
    <div class="tile-body">
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>Tag</th>
                        <th>Name</th>
                        <th>Qty</th>
                        <th>Category</th>
                        <th>Location</th>
                        <th>Custodian</th>
                        <th>Value</th>
                        <th>Status</th>
                        <th class="text-right" style="min-width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assets as $asset)
                        <tr>
                            <td><code>{{ $asset->asset_tag }}</code></td>
                            <td>
                                <strong>{{ $asset->name }}</strong>
                                @if($asset->batch_id)
                                    <br><small class="text-muted">Bulk item</small>
                                @endif
                                @if($asset->serial_number)
                                    <br><small class="text-muted">S/N: {{ $asset->serial_number }}</small>
                                @endif
                            </td>
                            <td>{{ $asset->quantity }}</td>
                            <td>{{ $asset->category->label() }}</td>
                            <td>{{ $asset->location ?? '—' }}</td>
                            <td>{{ $asset->custodian?->full_name ?? '—' }}</td>
                            <td>
                                @if($asset->current_value)
                                    {{ number_format($asset->current_value, 0) }} TZS
                                @elseif($asset->purchase_value)
                                    {{ number_format($asset->purchase_value, 0) }} TZS
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $asset->status->badgeClass() }}">{{ $asset->status->label() }}</span>
                            </td>
                            <td class="text-right text-nowrap">
                                <a href="{{ route('church.assets.show', $asset) }}" class="btn btn-sm btn-info" title="View">
                                    <i class="fa fa-eye"></i>
                                </a>
                                @can('update', $asset)
                                    <a href="{{ route('church.assets.edit', $asset) }}" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('church.assets.destroy', $asset) }}" class="d-inline"
                                        data-swal-confirm="Delete asset {{ $asset->name }}? This cannot be undone."
                                        data-swal-delete
                                        data-swal-confirm-text="Yes, delete">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                No assets recorded yet.
                                @can('create', \App\Models\ChurchAsset::class)
                                    <a href="{{ route('church.assets.create') }}">Record the first asset</a>.
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $assets->links() }}
    </div>
</div>
@endsection
