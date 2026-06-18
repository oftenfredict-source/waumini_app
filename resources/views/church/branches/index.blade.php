@extends('layouts.church')

@section('title', 'Branches')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-code-fork"></i> Branches</h1>
        <p>Manage your church branches</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">Branches</li>
    </ul>
</div>

<div class="row mb-3">
    <div class="col-md-8">
        <form method="GET" class="form-inline">
            <input type="text" name="search" class="form-control mr-2 mb-2" placeholder="Search branch..."
                value="{{ $filters['search'] ?? '' }}">
            <select name="status" class="form-control mr-2 mb-2">
                <option value="">All statuses</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
            </select>
            <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-search"></i> Search</button>
        </form>
    </div>
    <div class="col-md-4 text-md-right">
        @can('create', \App\Models\ChurchBranch::class)
            <a href="{{ route('church.branches.create') }}" class="btn btn-primary mb-2">
                <i class="fa fa-plus"></i> Add Branch
            </a>
        @endcan
    </div>
</div>

<div class="tile">
    <div class="table-responsive">
        <table class="table table-hover table-bordered mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Code</th>
                    <th>City</th>
                    <th>Pastor</th>
                    <th>Members</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($branches as $branch)
                    <tr>
                        <td>
                            {{ $branch->name }}
                            @if($branch->is_headquarters)
                                <span class="badge badge-primary ml-1">Headquarters</span>
                            @endif
                        </td>
                        <td><code>{{ $branch->code }}</code></td>
                        <td>{{ $branch->city ?? '—' }}</td>
                        <td>{{ $branch->pastor_name ?? '—' }}</td>
                        <td>{{ $branch->members_count }}</td>
                        <td>
                            <span class="badge badge-{{ $branch->is_active ? 'success' : 'secondary' }}">
                                {{ $branch->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-nowrap">
                            <a href="{{ route('church.branches.show', $branch) }}" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>
                            @can('update', $branch)
                                <a href="{{ route('church.branches.edit', $branch) }}" class="btn btn-sm btn-warning"><i class="fa fa-pencil"></i></a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No branches found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($branches->hasPages())
        <div class="mt-3">{{ $branches->links() }}</div>
    @endif
</div>
@endsection
