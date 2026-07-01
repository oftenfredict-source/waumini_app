@php $branch = $branch ?? null; @endphp
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>{{ __('pages.branches.branch_name') }} <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $branch?->name) }}" required placeholder="{{ __('pages.branches.name_placeholder') }}">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>{{ __('pages.branches.branch_code') }} <span class="text-danger">*</span></label>
            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                   value="{{ old('code', $branch?->code) }}" required placeholder="{{ __('pages.branches.code_placeholder') }}">
            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <small class="text-muted">{{ __('pages.branches.code_hint') }}</small>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>{{ __('pages.branches.branch_pastor') }}</label>
            <input type="text" name="pastor_name" class="form-control" value="{{ old('pastor_name', $branch?->pastor_name) }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>{{ __('common.phone') }}</label>
            <input type="text" name="phone" class="form-control" value="{{ old('phone', $branch?->phone) }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>{{ __('common.email') }}</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $branch?->email) }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>{{ __('pages.shared.city') }}</label>
            <input type="text" name="city" class="form-control" value="{{ old('city', $branch?->city) }}">
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label>{{ __('pages.shared.address') }}</label>
            <textarea name="address" class="form-control" rows="2">{{ old('address', $branch?->address) }}</textarea>
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label>{{ __('pages.branches.branch_logo') }}</label>
            @if($branch?->logoUrl())
                <div class="mb-2">
                    <img src="{{ $branch->logoUrl() }}" alt="{{ $branch->name }}" style="max-height:60px;max-width:140px;object-fit:contain;">
                </div>
                <div class="form-check mb-2">
                    <input type="checkbox" name="remove_logo" value="1" class="form-check-input" id="remove_logo" @checked(old('remove_logo'))>
                    <label class="form-check-label" for="remove_logo">{{ __('pages.branches.remove_logo') }}</label>
                </div>
            @endif
            <input type="file" name="logo" class="form-control-file @error('logo') is-invalid @enderror" accept="image/jpeg,image/png,image/webp">
            @error('logo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-check mt-2">
            <input type="checkbox" name="is_headquarters" value="1" class="form-check-input" id="is_headquarters"
                   @checked(old('is_headquarters', $branch?->is_headquarters))>
            <label class="form-check-label" for="is_headquarters">{{ __('pages.branches.set_headquarters') }}</label>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-check mt-2">
            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active"
                   @checked(old('is_active', $branch?->is_active ?? true))>
            <label class="form-check-label" for="is_active">{{ __('pages.branches.branch_active') }}</label>
        </div>
    </div>
</div>
