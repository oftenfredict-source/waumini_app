@php
    $user = $user ?? null;
    $selectedRole = old('role', $user?->roles->first()?->name ?? \App\Enums\ChurchStaffRole::Secretary->value);
@endphp

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>{{ __('pages.shared.full_name') }} *</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                value="{{ old('name', $user?->name) }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>{{ __('pages.system_users.email_login') }} *</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                value="{{ old('email', $user?->email) }}" required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>{{ __('common.phone') }}</label>
            <input type="text" name="phone" class="form-control" value="{{ old('phone', $user?->phone) }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>{{ __('common.role') }} *</label>
            <select name="role" class="form-control @error('role') is-invalid @enderror" required>
                @foreach($roles as $role)
                    <option value="{{ $role->value }}" @selected($selectedRole === $role->value)>{{ $role->label() }}</option>
                @endforeach
            </select>
            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    @if($user)
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('common.status') }}</label>
                <select name="status" class="form-control">
                    @foreach(\App\Enums\UserStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(old('status', $user->status->value) === $status->value)>
                            {{ ucfirst($status->value) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    @endif
    <div class="col-md-6">
        @if(($church->branches_enabled ?? false) && ($branches ?? collect())->isNotEmpty())
            <div class="form-group">
                <label>{{ __('common.branch') }}</label>
                <select name="branch_id" class="form-control">
                    <option value="">{{ __('pages.system_users.headquarters_all_branches') }}</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) old('branch_id', $user?->branch_id) === (string) $branch->id)>{{ $branch->displayLabel() }}</option>
                    @endforeach
                </select>
                <small class="text-muted">{{ __('pages.system_users.branch_hint') }}</small>
            </div>
        @endif
    </div>
</div>

@if(! $user)
    <p class="text-muted"><i class="fa fa-info-circle"></i> {{ __('pages.system_users.password_auto_generated') }}</p>
@endif
