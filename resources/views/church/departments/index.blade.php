@extends('layouts.church')

@section('title', __('pages.departments.title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-sitemap',
    'title' => __('pages.departments.title'),
    'subtitle' => __('pages.departments.subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.departments')],
    ],
])

<div class="row mb-3">
    <div class="col-md-8">
        <form method="GET" class="form-inline">
            <input type="text" name="search" class="form-control mr-2 mb-2" placeholder="{{ __('pages.departments.search_placeholder') }}"
                value="{{ $filters['search'] ?? '' }}">
            <select name="status" class="form-control mr-2 mb-2">
                <option value="">{{ __('pages.shared.all_statuses') }}</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>{{ __('common.active') }}</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>{{ __('common.inactive') }}</option>
            </select>
            <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-search"></i> {{ __('common.search') }}</button>
        </form>
    </div>
    <div class="col-md-4 text-md-right">
        @can('create', \App\Models\Department::class)
            <a href="{{ route('church.departments.create') }}" class="btn btn-primary mb-2">
                <i class="fa fa-plus"></i> {{ __('pages.departments.add_department') }}
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
                        <th>{{ __('pages.shared.department_name') }}</th>
                        <th>{{ __('pages.shared.leader_col') }}</th>
                        <th>{{ __('pages.shared.members') }}</th>
                        <th>{{ __('common.status') }}</th>
                        <th>{{ __('pages.shared.created_col') }}</th>
                        <th class="text-right" style="min-width: 200px;">{{ __('common.actions') }}</th>
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
                                <a href="{{ route('church.departments.show', $department) }}" class="btn btn-sm btn-info" title="{{ __('common.view') }}">
                                    <i class="fa fa-eye"></i>
                                </a>
                                @can('update', $department)
                                    <a href="{{ route('church.departments.edit', $department) }}" class="btn btn-sm btn-warning" title="{{ __('common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-primary js-assign-leader"
                                        title="{{ __('pages.departments.assign_leader') }}"
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
                                        data-swal-confirm-text="{{ __('common.yes_delete') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="{{ __('common.delete') }}">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                {{ __('pages.departments.empty') }}
                                @can('create', \App\Models\Department::class)
                                    <a href="{{ route('church.departments.create') }}">{{ __('pages.departments.add_department_link') }}</a>.
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
                    <h5 class="modal-title" id="assignLeaderModalLabel">{{ __('pages.departments.assign_leader') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('common.close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3" id="assignLeaderDepartmentName"></p>
                    <div class="form-group mb-0">
                        <label>{{ __('pages.departments.department_leader') }}</label>
                        <select name="head_id" id="assignLeaderSelect" class="form-control">
                            <option value="">{{ __('pages.departments.no_leader') }}</option>
                            @foreach($members as $member)
                                <option value="{{ $member->id }}">
                                    {{ $member->full_name }} ({{ $member->member_number }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> {{ __('pages.departments.save_leader') }}
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
            nameEl.textContent = @json(__('pages.departments.department_label')).replace(':name', button.dataset.departmentName || '');
            titleEl.textContent = @json(__('pages.departments.assign_leader_title')).replace(':name', button.dataset.departmentName || '');
            select.value = button.dataset.headId || '';
        });
    });
});
</script>
@endpush
