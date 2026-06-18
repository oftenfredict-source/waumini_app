@extends('layouts.church')

@section('title', 'Departments')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-sitemap"></i> Departments</h1>
        <p>Manage church departments and ministries</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">Departments</li>
    </ul>
</div>

<div class="row mb-3">
    <div class="col-md-8">
        <form method="GET" class="form-inline">
            <input type="text" name="search" class="form-control mr-2 mb-2" placeholder="Search department..."
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
        @can('create', \App\Models\Department::class)
            <a href="{{ route('church.departments.create') }}" class="btn btn-primary mb-2">
                <i class="fa fa-plus"></i> Add Department
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
                        <th>Department Name</th>
                        <th>Leader</th>
                        <th>Members</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-right" style="min-width: 200px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $department)
                        <tr>
                            <td>
                                <strong>{{ $department->name }}</strong>
                                @if($department->description)
                                    <br><small class="text-muted">{{ Str::limit($department->description, 60) }}</small>
                                @endif
                            </td>
                            <td>{{ $department->head?->full_name ?? '—' }}</td>
                            <td>{{ $department->members_count }}</td>
                            <td>
                                <span class="badge badge-{{ $department->status->value === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($department->status->value) }}
                                </span>
                            </td>
                            <td>{{ $department->created_at->format('M d, Y') }}</td>
                            <td class="text-right text-nowrap">
                                <a href="{{ route('church.departments.show', $department) }}" class="btn btn-sm btn-info" title="View">
                                    <i class="fa fa-eye"></i>
                                </a>
                                @can('update', $department)
                                    <a href="{{ route('church.departments.edit', $department) }}" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-primary js-assign-leader"
                                        title="Assign leader"
                                        data-toggle="modal"
                                        data-target="#assignLeaderModal"
                                        data-department-name="{{ $department->name }}"
                                        data-action="{{ route('church.departments.assign-head', $department) }}"
                                        data-head-id="{{ $department->head_id }}">
                                        <i class="fa fa-user"></i>
                                    </button>
                                    <form method="POST" action="{{ route('church.departments.destroy', $department) }}" class="d-inline"
                                        data-swal-confirm="Delete {{ $department->name }}? This cannot be undone."
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
                            <td colspan="6" class="text-center text-muted py-4">
                                No departments found.
                                @can('create', \App\Models\Department::class)
                                    <a href="{{ route('church.departments.create') }}">Add a department</a>.
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $departments->links() }}
    </div>
</div>

@can('departments.manage')
<div class="modal fade" id="assignLeaderModal" tabindex="-1" role="dialog" aria-labelledby="assignLeaderModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" id="assignLeaderForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="assignLeaderModalLabel">Assign Department Leader</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3" id="assignLeaderDepartmentName"></p>
                    <div class="form-group mb-0">
                        <label>Department Leader</label>
                        <select name="head_id" id="assignLeaderSelect" class="form-control">
                            <option value="">No leader assigned</option>
                            @foreach($members as $member)
                                <option value="{{ $member->id }}">
                                    {{ $member->full_name }} ({{ $member->member_number }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Save Leader
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-assign-leader').forEach(function (button) {
        button.addEventListener('click', function () {
            var form = document.getElementById('assignLeaderForm');
            var select = document.getElementById('assignLeaderSelect');
            var nameEl = document.getElementById('assignLeaderDepartmentName');
            var titleEl = document.getElementById('assignLeaderModalLabel');

            if (!form || !select) return;

            form.action = button.dataset.action || '';
            nameEl.textContent = 'Department: ' + (button.dataset.departmentName || '');
            titleEl.textContent = 'Assign Leader — ' + (button.dataset.departmentName || '');
            select.value = button.dataset.headId || '';
        });
    });
});
</script>
@endpush
