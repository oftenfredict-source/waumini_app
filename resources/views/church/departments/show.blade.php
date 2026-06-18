@extends('layouts.church')

@section('title', $department->name)

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-sitemap"></i> {{ $department->name }}</h1>
        <p>Department details and member management</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.departments.index') }}">Departments</a></li>
        <li class="breadcrumb-item">{{ $department->name }}</li>
    </ul>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="tile mb-3">
            <h3 class="tile-title">Department Information</h3>
            <table class="table table-borderless mb-0">
                <tr><th width="180">Name</th><td>{{ $department->name }}</td></tr>
                <tr><th>Status</th>
                    <td>
                        <span class="badge badge-{{ $department->status->value === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($department->status->value) }}
                        </span>
                    </td>
                </tr>
                <tr><th>Department Leader</th>
                    <td>
                        @if($department->head)
                            <a href="{{ route('church.members.show', $department->head) }}">{{ $department->head->full_name }}</a>
                            <span class="text-muted">({{ $department->head->member_number }})</span>
                        @else
                            —
                        @endif
                    </td>
                </tr>
                <tr><th>Members</th><td>{{ $department->members->count() }}</td></tr>
                <tr><th>Description</th><td>{{ $department->description ?? '—' }}</td></tr>
                <tr><th>Created</th><td>{{ $department->created_at->format('M d, Y H:i') }}</td></tr>
            </table>
        </div>

        @can('update', $department)
            <div class="tile mb-3">
                <h3 class="tile-title"><i class="fa fa-user"></i> Assign Leader</h3>
                <form method="POST" action="{{ route('church.departments.assign-head', $department) }}">
                    @csrf
                    <div class="row align-items-end">
                        <div class="col-md-8">
                            <div class="form-group mb-md-0">
                                <label>Department Leader</label>
                                <select name="head_id" class="form-control">
                                    <option value="">No leader assigned</option>
                                    @foreach($members as $member)
                                        <option value="{{ $member->id }}" @selected($department->head_id == $member->id)>
                                            {{ $member->full_name }} ({{ $member->member_number }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fa fa-save"></i> Save Leader
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @endcan

        <div class="tile">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="tile-title mb-0"><i class="fa fa-users"></i> Department Members</h3>
                <span class="badge badge-primary">{{ $department->members->count() }}</span>
            </div>

            @can('update', $department)
                @if($availableMembers->isNotEmpty())
                    <form method="POST" action="{{ route('church.departments.members.attach', $department) }}" class="mb-4">
                        @csrf
                        <div class="form-group">
                            <label>Add Members</label>
                            <select name="member_ids[]" class="form-control" multiple size="5">
                                @foreach($availableMembers as $member)
                                    <option value="{{ $member->id }}">
                                        {{ $member->full_name }} ({{ $member->member_number }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Hold Ctrl (or Cmd on Mac) to select multiple members.</small>
                        </div>
                        <button type="submit" class="btn btn-outline-primary btn-sm">
                            <i class="fa fa-plus"></i> Add Selected Members
                        </button>
                    </form>
                @endif
            @endcan

            @if($department->members->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Member ID</th>
                                <th>Role</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($department->members->sortBy('full_name') as $member)
                                <tr>
                                    <td>
                                        <a href="{{ route('church.members.show', $member) }}">{{ $member->full_name }}</a>
                                        @if($department->head_id === $member->id)
                                            <span class="badge badge-warning ml-1">Leader</span>
                                        @endif
                                    </td>
                                    <td><code>{{ $member->member_number }}</code></td>
                                    <td>{{ ucfirst($member->pivot->role ?? 'member') }}</td>
                                    <td class="text-right">
                                        @can('update', $department)
                                            <form method="POST" action="{{ route('church.departments.members.remove', [$department, $member]) }}" class="d-inline"
                                                data-swal-confirm="Remove {{ $member->full_name }} from {{ $department->name }}?"
                                                data-swal-delete
                                                data-swal-confirm-text="Yes, remove">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove member">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted mb-0">No members assigned to this department yet.</p>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <div class="tile">
            <h3 class="tile-title">Actions</h3>
            <a href="{{ route('church.departments.index') }}" class="btn btn-secondary btn-block mb-2">
                <i class="fa fa-arrow-left"></i> Back to Departments
            </a>
            @can('update', $department)
                <a href="{{ route('church.departments.edit', $department) }}" class="btn btn-warning btn-block mb-2">
                    <i class="fa fa-pencil"></i> Edit Department
                </a>
                <form method="POST" action="{{ route('church.departments.destroy', $department) }}"
                    data-swal-confirm="Delete {{ $department->name }}? This cannot be undone."
                    data-swal-delete
                    data-swal-confirm-text="Yes, delete">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block">
                        <i class="fa fa-trash"></i> Delete Department
                    </button>
                </form>
            @endcan
        </div>
    </div>
</div>
@endsection
