@extends('layouts.church')

@section('title', $branch->name)

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-code-fork"></i> {{ $branch->name }}</h1>
        <p><code>{{ $branch->code }}</code></p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.branches.index') }}">Branches</a></li>
        <li class="breadcrumb-item">{{ $branch->code }}</li>
    </ul>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="tile mb-3">
            @if($branch->logoUrl())
                <div class="mb-3">
                    <img src="{{ $branch->logoUrl() }}" alt="{{ $branch->name }}" style="max-height:80px;max-width:180px;object-fit:contain;">
                </div>
            @endif
            <table class="table table-borderless table-sm mb-0">
                <tr><th width="180">Code</th><td><code>{{ $branch->code }}</code></td></tr>
                <tr><th>Type</th><td>{{ $branch->is_headquarters ? 'Headquarters' : 'Branch' }}</td></tr>
                <tr><th>Pastor</th><td>{{ $branch->pastor_name ?? '—' }}</td></tr>
                <tr><th>Phone</th><td>{{ $branch->phone ?? '—' }}</td></tr>
                <tr><th>Email</th><td>{{ $branch->email ?? '—' }}</td></tr>
                <tr><th>City</th><td>{{ $branch->city ?? '—' }}</td></tr>
                <tr><th>Address</th><td>{{ $branch->address ?? '—' }}</td></tr>
                <tr><th>Members</th><td>{{ $branch->members_count }}</td></tr>
                <tr><th>Leaders</th><td>{{ $branch->leaders_count }}</td></tr>
                <tr><th>Status</th><td>
                    <span class="badge badge-{{ $branch->is_active ? 'success' : 'secondary' }}">
                        {{ $branch->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td></tr>
            </table>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="tile">
            @can('update', $branch)
                <a href="{{ route('church.branches.edit', $branch) }}" class="btn btn-warning btn-block mb-2">
                    <i class="fa fa-pencil"></i> Edit Branch
                </a>
            @endcan
            <a href="{{ route('church.members.index', ['branch_id' => $branch->id]) }}" class="btn btn-info btn-block">
                <i class="fa fa-users"></i> View Members
            </a>
        </div>
    </div>
</div>
@endsection
