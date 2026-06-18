@extends('layouts.church')

@section('title', 'Roles & Permissions')

@push('styles')
<style>
    .roles-intro {
        border-left: 4px solid var(--waumini-primary, #5c6bc0);
        background: #f8f9fc;
        padding: 1rem 1.25rem;
        border-radius: 0 4px 4px 0;
        margin-bottom: 1.5rem;
    }

    .roles-intro p {
        margin-bottom: 0;
        color: #5c6873;
    }

    .role-tabs .nav-link {
        font-weight: 500;
        color: #5c6873;
        border: none;
        border-bottom: 3px solid transparent;
        padding: 0.75rem 1.25rem;
    }

    .role-tabs .nav-link:hover {
        color: var(--waumini-primary, #5c6bc0);
        border-color: transparent;
    }

    .role-tabs .nav-link.active {
        color: var(--waumini-primary, #5c6bc0);
        background: transparent;
        border-bottom-color: var(--waumini-primary, #5c6bc0);
    }

    .role-tabs .badge {
        font-size: 0.7rem;
        font-weight: 600;
        vertical-align: middle;
        margin-left: 0.35rem;
    }

    .permission-group {
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e9ecef;
    }

    .permission-group:last-of-type {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .permission-group__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .permission-group__title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0;
        font-size: 0.95rem;
        font-weight: 600;
        color: #2a2c36;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .permission-group__title i {
        color: var(--waumini-primary, #5c6bc0);
        width: 1.25rem;
        text-align: center;
    }

    .permission-group__actions {
        display: flex;
        gap: 0.75rem;
        font-size: 0.8rem;
    }

    .permission-group__actions button {
        background: none;
        border: none;
        padding: 0;
        color: var(--waumini-primary, #5c6bc0);
        cursor: pointer;
        font-weight: 500;
    }

    .permission-group__actions button:hover {
        text-decoration: underline;
    }

    .permission-card {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        height: 100%;
        padding: 0.85rem 1rem;
        background: #f8f9fc;
        border: 1px solid #e4e7ef;
        border-radius: 6px;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
        cursor: pointer;
    }

    .permission-card:hover {
        border-color: var(--waumini-primary, #5c6bc0);
        box-shadow: 0 2px 6px rgba(92, 107, 192, 0.12);
    }

    .permission-card.is-checked {
        background: #fff;
        border-color: var(--waumini-primary, #5c6bc0);
    }

    .permission-card input[type="checkbox"] {
        margin-top: 0.2rem;
        flex-shrink: 0;
        cursor: pointer;
    }

    .permission-card__label {
        margin: 0;
        cursor: pointer;
        line-height: 1.35;
    }

    .permission-card__label strong {
        display: block;
        font-size: 0.9rem;
        color: #2a2c36;
        font-weight: 600;
    }

    .permission-card__label small {
        display: block;
        margin-top: 0.2rem;
        font-size: 0.78rem;
        color: #8a93a2;
        font-family: monospace;
    }

    .role-form-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        margin-top: 1.5rem;
        padding-top: 1.25rem;
        border-top: 1px solid #e9ecef;
    }

    .role-form-footer__hint {
        font-size: 0.85rem;
        color: #8a93a2;
        margin: 0;
    }

    @media (max-width: 767px) {
        .role-tabs {
            flex-wrap: nowrap;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-bottom: 1px solid #dee2e6;
        }

        .role-tabs .nav-item {
            flex-shrink: 0;
        }

        .role-form-footer {
            flex-direction: column;
            align-items: stretch;
        }

        .role-form-footer .btn {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
@include('church.system.partials.nav')

<div class="app-title">
    <div>
        <h1><i class="fa fa-shield"></i> Roles & Permissions</h1>
        <p>Configure what each church role can access at {{ $church->name }}</p>
    </div>
</div>

<div class="roles-intro">
    <p>
        <i class="fa fa-info-circle text-primary"></i>
        Select a role below, then enable or disable permissions by category. Changes apply only after you save that role.
    </p>
</div>

<div class="tile">
    <ul class="nav nav-tabs role-tabs" role="tablist">
        @foreach($roles as $index => $role)
            @php
                $roleLabel = config('church.roles.'.$role->name, ucfirst($role->name));
                $assignedCount = $role->permissions->count();
            @endphp
            <li class="nav-item">
                <a class="nav-link @if($index === 0) active @endif"
                   id="role-tab-{{ $role->name }}"
                   data-toggle="tab"
                   href="#role-panel-{{ $role->name }}"
                   role="tab">
                    {{ $roleLabel }}
                    <span class="badge badge-primary">{{ $assignedCount }}</span>
                </a>
            </li>
        @endforeach
    </ul>

    <div class="tab-content p-4">
        @foreach($roles as $index => $role)
            @php
                $roleLabel = config('church.roles.'.$role->name, ucfirst($role->name));
                $rolePermissionNames = $role->permissions->pluck('name')->all();
            @endphp
            <div class="tab-pane fade @if($index === 0) show active @endif"
                 id="role-panel-{{ $role->name }}"
                 role="tabpanel">

                <form method="POST"
                      action="{{ route('church.system.roles.update') }}"
                      class="role-permissions-form"
                      data-role="{{ $role->name }}">
                    @csrf
                    <input type="hidden" name="role" value="{{ $role->name }}">

                    @foreach($permissions as $group => $groupPermissions)
                        @php
                            $groupMeta = config('church.permission_groups.'.$group, [
                                'label' => ucfirst(str_replace('_', ' ', $group)),
                                'icon' => 'fa-folder-o',
                            ]);
                        @endphp
                        <div class="permission-group" data-group="{{ $role->name }}_{{ $group }}">
                            <div class="permission-group__header">
                                <h4 class="permission-group__title">
                                    <i class="fa {{ $groupMeta['icon'] }}"></i>
                                    {{ $groupMeta['label'] }}
                                </h4>
                                <div class="permission-group__actions">
                                    <button type="button" class="js-select-all" data-target="{{ $role->name }}_{{ $group }}">
                                        Select all
                                    </button>
                                    <button type="button" class="js-clear-all" data-target="{{ $role->name }}_{{ $group }}">
                                        Clear all
                                    </button>
                                </div>
                            </div>

                            <div class="row">
                                @foreach($groupPermissions as $permission)
                                    @php
                                        [$groupKey, $action] = array_pad(explode('.', $permission->name, 2), 2, null);
                                        $actionLabel = config('church.permission_actions.'.$action, ucfirst(str_replace('_', ' ', $action ?? $permission->name)));
                                        $isSystemAction = $groupKey === 'system' && $action;
                                        $displayLabel = $isSystemAction
                                            ? $actionLabel
                                            : trim($actionLabel.' '.$groupMeta['label']);
                                        $isChecked = in_array($permission->name, $rolePermissionNames, true);
                                        $inputId = 'perm_'.$role->name.'_'.$permission->id;
                                    @endphp
                                    <div class="col-md-6 col-lg-4 col-xl-3 mb-3">
                                        <label class="permission-card @if($isChecked) is-checked @endif" for="{{ $inputId }}">
                                            <input type="checkbox"
                                                   class="form-check-input js-permission-checkbox"
                                                   name="permissions[]"
                                                   value="{{ $permission->name }}"
                                                   id="{{ $inputId }}"
                                                   data-group="{{ $role->name }}_{{ $group }}"
                                                   @checked($isChecked)>
                                            <span class="permission-card__label">
                                                <strong>{{ $displayLabel }}</strong>
                                                <small>{{ $permission->name }}</small>
                                            </span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <div class="role-form-footer">
                        <p class="role-form-footer__hint">
                            <span class="js-selected-count" data-role="{{ $role->name }}">
                                {{ count($rolePermissionNames) }}
                            </span>
                            of {{ $permissions->flatten()->count() }} permissions enabled for {{ $roleLabel }}
                        </p>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Save {{ $roleLabel }} Permissions
                        </button>
                    </div>
                </form>
            </div>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    function updateCardState(checkbox) {
        var card = checkbox.closest('.permission-card');
        if (card) {
            card.classList.toggle('is-checked', checkbox.checked);
        }
    }

    function updateRoleCount(roleName) {
        var form = document.querySelector('.role-permissions-form[data-role="' + roleName + '"]');
        if (!form) return;
        var checked = form.querySelectorAll('.js-permission-checkbox:checked').length;
        var counter = document.querySelector('.js-selected-count[data-role="' + roleName + '"]');
        if (counter) {
            counter.textContent = checked;
        }
        var tabBadge = document.querySelector('#role-tab-' + roleName + ' .badge');
        if (tabBadge) {
            tabBadge.textContent = checked;
        }
    }

    document.querySelectorAll('.js-permission-checkbox').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            updateCardState(checkbox);
            var form = checkbox.closest('.role-permissions-form');
            if (form) {
                updateRoleCount(form.dataset.role);
            }
        });
    });

    document.querySelectorAll('.js-select-all').forEach(function (button) {
        button.addEventListener('click', function () {
            var target = button.dataset.target;
            document.querySelectorAll('.js-permission-checkbox[data-group="' + target + '"]').forEach(function (checkbox) {
                checkbox.checked = true;
                updateCardState(checkbox);
            });
            var form = button.closest('.role-permissions-form');
            if (form) {
                updateRoleCount(form.dataset.role);
            }
        });
    });

    document.querySelectorAll('.js-clear-all').forEach(function (button) {
        button.addEventListener('click', function () {
            var target = button.dataset.target;
            document.querySelectorAll('.js-permission-checkbox[data-group="' + target + '"]').forEach(function (checkbox) {
                checkbox.checked = false;
                updateCardState(checkbox);
            });
            var form = button.closest('.role-permissions-form');
            if (form) {
                updateRoleCount(form.dataset.role);
            }
        });
    });
})();
</script>
@endpush
