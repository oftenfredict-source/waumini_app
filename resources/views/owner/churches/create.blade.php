@extends('layouts.owner')

@section('title', __('owner.church.create_title'))

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-plus"></i> {{ __('owner.church.create_title') }}</h1>
        <p>{{ __('owner.church.create_subtitle') }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('owner.churches.index') }}">{{ __('owner.churches') }}</a></li>
        <li class="breadcrumb-item">{{ __('owner.add') }}</li>
    </ul>
</div>

<div class="tile">
    <form method="POST" action="{{ route('owner.churches.store') }}">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('owner.church.church_name') }} *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('owner.church.subdomain_slug') }}</label>
                    <input type="text" name="slug" class="form-control" value="{{ old('slug') }}" placeholder="{{ __('owner.church.auto_generated') }}">
                    <small class="text-muted">{{ __('owner.church.will_be', ['slug' => 'slug', 'domain' => config('waumini.base_domain')]) }}</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('owner.church.church_email') }} *</label>
                    <input type="email" name="email" id="church_email" class="form-control" value="{{ old('email') }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('owner.phone') }}</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('owner.church.pastor') }}</label>
                    <input type="text" name="pastor_name" class="form-control" value="{{ old('pastor_name') }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('owner.church.member_id_prefix') }}</label>
                    <input type="text" name="member_id_prefix" class="form-control" value="{{ old('member_id_prefix') }}"
                        maxlength="6" placeholder="{{ __('owner.church.auto_generated') }}">
                    <small class="text-muted">{{ __('owner.church.member_id_help', ['year' => now()->format('Y')]) }}</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('owner.church.denomination') }}</label>
                    <input type="text" name="denomination" class="form-control" value="{{ old('denomination') }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>{{ __('owner.church.city') }}</label>
                    <input type="text" name="city" class="form-control" value="{{ old('city') }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>{{ __('owner.church.country') }}</label>
                    <input type="text" name="country" class="form-control" value="{{ old('country') }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>{{ __('owner.church.currency') }}</label>
                    @include('owner.settings.partials.currency-select', [
                        'fieldName' => 'currency',
                        'fieldValue' => old('currency', $defaultCurrency),
                        'required' => true,
                    ])
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label>{{ __('owner.church.address') }}</label>
                    <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('owner.church.subscription_package') }}</label>
                    <select name="package_id" class="form-control">
                        @foreach($packages as $package)
                            <option value="{{ $package->id }}" @selected(old('package_id') == $package->id)>
                                {{ $package->name }} — {{ __('owner.subs.installation') }} {{ $platformCurrency }} {{ number_format($package->installation_price, 2) }}, {{ $platformCurrency }} {{ number_format($package->yearly_price, 2) }}/yr
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('owner.church.billing') }}</label>
                    <input type="hidden" name="billing_cycle" value="yearly">
                    <input type="text" class="form-control" value="{{ __('owner.church.yearly_billing') }}" readonly>
                    <small class="text-muted">{{ __('owner.church.billing_help') }}</small>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input type="hidden" name="branches_enabled" value="0">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="branches_enabled" name="branches_enabled" value="1"
                            @checked(old('branches_enabled', false))>
                        <label class="custom-control-label" for="branches_enabled">{{ __('owner.church.enable_branches') }}</label>
                    </div>
                    <small class="text-muted">{{ __('owner.church.branches_help') }}</small>
                </div>
            </div>
        </div>

        <hr>
        <h5 class="mb-3"><i class="fa fa-user"></i> {{ __('owner.church.admin_section') }}</h5>
        <p class="text-muted">{{ __('owner.church.admin_password_help') }}</p>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('owner.church.login_email') }} *</label>
                    <input type="email" name="admin_email" id="admin_email" class="form-control"
                        value="{{ old('admin_email') }}" placeholder="{{ __('owner.church.same_as_email') }}" required>
                    <small class="text-muted">{{ __('owner.church.login_email_help') }}</small>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('owner.church.create_church') }}</button>
            <a href="{{ route('owner.churches.index') }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var churchEmail = document.getElementById('church_email');
        var adminEmail = document.getElementById('admin_email');
        if (!churchEmail || !adminEmail) return;

        churchEmail.addEventListener('input', function () {
            if (!adminEmail.dataset.edited) {
                adminEmail.value = churchEmail.value;
            }
        });

        adminEmail.addEventListener('input', function () {
            adminEmail.dataset.edited = adminEmail.value !== churchEmail.value ? '1' : '';
        });

        if (!adminEmail.value && churchEmail.value) {
            adminEmail.value = churchEmail.value;
        }
    })();
</script>
@endpush
