@extends('layouts.church')

@section('title', $department->name)

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-sitemap',
    'title' => $department->name,
    'subtitle' => __('pages.departments.show_subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.departments'), 'route' => 'church.departments.index'],
        ['label' => $department->name],
    ],
])

<div class="row">
    <div class="col-lg-8">
        <div class="tile mb-3">
            <h3 class="tile-title">{{ __('pages.departments.information_title') }}</h3>
            <table class="table table-borderless mb-0">
                <tr><th width="180">{{ __('common.name') }}</th><td>{{ $department->name }}</td></tr>
                <tr><th>{{ __('common.status') }}</th>
                    <td>
                        <span class="badge badge-{{ $department->status->value === 'active' ? 'success' : 'secondary' }}">
                            {{ $department->status->label() }}
                        </span>
                    </td>
                </tr>
                <tr><th>{{ __('pages.departments.department_leader') }}</th>
                    <td>
                        @if($department->head)
                            <a href="{{ route('church.members.show', $department->head) }}">{{ $department->head->full_name }}</a>
                            <span class="text-muted">({{ $department->head->member_number }})</span>
                        @else
                            —
                        @endif
                    </td>
                </tr>
                <tr><th>{{ __('pages.shared.members') }}</th><td>{{ $department->members->count() }}</td></tr>
                <tr><th>{{ __('common.description') }}</th><td>{{ $department->description ?? '—' }}</td></tr>
                <tr><th>{{ __('common.created') }}</th><td>{{ $department->created_at->format('M d, Y H:i') }}</td></tr>
            </table>
        </div>

        @can('update', $department)
            <div class="tile mb-3">
                <h3 class="tile-title"><i class="fa fa-user"></i> {{ __('pages.departments.assign_leader') }}</h3>
                <form method="POST" action="{{ route('church.departments.assign-head', $department) }}">
                    @csrf
                    <div class="row align-items-end">
                        <div class="col-md-8">
                            <div class="form-group mb-md-0">
                                <label>{{ __('pages.departments.department_leader') }}</label>
                                <select name="head_id" class="form-control">
                                    <option value="">{{ __('pages.departments.no_leader') }}</option>
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
                                <i class="fa fa-save"></i> {{ __('pages.departments.save_leader') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @endcan

        <div class="tile">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="tile-title mb-0"><i class="fa fa-users"></i> {{ __('pages.departments.department_members') }}</h3>
                <span class="badge badge-primary">{{ $department->members->count() }}</span>
            </div>

            @can('update', $department)
                @if($availableMembers->isNotEmpty())
                    <form method="POST" action="{{ route('church.departments.members.attach', $department) }}" class="mb-4">
                        @csrf
                        <div class="form-group">
                            <label>{{ __('pages.departments.add_members') }}</label>
                            <select name="member_ids[]" class="form-control" multiple size="5">
                                @foreach($availableMembers as $member)
                                    <option value="{{ $member->id }}">
                                        {{ $member->full_name }} ({{ $member->member_number }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">{{ __('pages.shared.multi_select_hint_short') }}</small>
                        </div>
                        <button type="submit" class="btn btn-outline-primary btn-sm">
                            <i class="fa fa-plus"></i> {{ __('pages.departments.add_selected_members') }}
                        </button>
                    </form>
                @endif
            @endcan

            @if($department->members->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('common.member') }}</th>
                                <th>{{ __('pages.shared.member_id') }}</th>
                                <th>{{ __('common.role') }}</th>
                                <th class="text-right">{{ __('common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($department->members->sortBy('full_name') as $member)
                                <tr>
                                    <td>
                                        <a href="{{ route('church.members.show', $member) }}">{{ $member->full_name }}</a>
                                        @if($department->head_id === $member->id)
                                            <span class="badge badge-warning ml-1">{{ __('pages.shared.leader_col') }}</span>
                                        @endif
                                    </td>
                                    <td><code>{{ $member->member_number }}</code></td>
                                    <td>{{ ucfirst($member->pivot->role ?? 'member') }}</td>
                                    <td class="text-right">
                                        @can('update', $department)
                                            <form method="POST" action="{{ route('church.departments.members.remove', [$department, $member]) }}" class="d-inline"
                                                data-swal-confirm="{{ __('pages.shared.remove_confirm', ['member' => $member->full_name, 'department' => $department->name]) }}"
                                                data-swal-delete
                                                data-swal-confirm-text="{{ __('pages.shared.yes_remove') }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('pages.shared.remove_member') }}">
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
                <p class="text-muted mb-0">{{ __('pages.departments.no_members_assigned') }}</p>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <div class="tile">
            <h3 class="tile-title">{{ __('common.actions') }}</h3>
            <a href="{{ route('church.departments.index') }}" class="btn btn-secondary btn-block mb-2">
                <i class="fa fa-arrow-left"></i> {{ __('pages.shared.back_to', ['module' => __('pages.departments.title')]) }}
            </a>
            @can('update', $department)
                <a href="{{ route('church.departments.edit', $department) }}" class="btn btn-warning btn-block mb-2">
                    <i class="fa fa-pencil"></i> {{ __('pages.shared.edit_item', ['item' => __('pages.departments.item')]) }}
                </a>
                <form method="POST" action="{{ route('church.departments.destroy', $department) }}"
                    data-swal-confirm="{{ __('pages.shared.delete_confirm_named', ['name' => $department->name]) }}"
                    data-swal-delete
                    data-swal-confirm-text="{{ __('common.yes_delete') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block">
                        <i class="fa fa-trash"></i> {{ __('pages.shared.delete_item', ['item' => __('pages.departments.item')]) }}
                    </button>
                </form>
            @endcan
        </div>
    </div>
</div>
@endsection
