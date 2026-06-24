@extends('layouts.owner')

@section('title', 'Add Church')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-plus"></i> Add Church</h1>
        <p>Create a new church account</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('owner.churches.index') }}">Churches</a></li>
        <li class="breadcrumb-item">Add</li>
    </ul>
</div>

<div class="tile">
    <form method="POST" action="{{ route('owner.churches.store') }}">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Church Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Subdomain Slug</label>
                    <input type="text" name="slug" class="form-control" value="{{ old('slug') }}" placeholder="auto-generated if empty">
                    <small class="text-muted">Will be: slug.{{ config('waumini.base_domain') }}</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Church Contact Email *</label>
                    <input type="email" name="email" id="church_email" class="form-control" value="{{ old('email') }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Pastor Name</label>
                    <input type="text" name="pastor_name" class="form-control" value="{{ old('pastor_name') }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Member ID Prefix</label>
                    <input type="text" name="member_id_prefix" class="form-control" value="{{ old('member_id_prefix') }}"
                        maxlength="6" placeholder="Auto from church name (e.g. TAG IMANI → IM)">
                    <small class="text-muted">Members will get IDs like IM-{{ now()->format('Y') }}-0001. Must be unique across all churches.</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Denomination</label>
                    <input type="text" name="denomination" class="form-control" value="{{ old('denomination') }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>City</label>
                    <input type="text" name="city" class="form-control" value="{{ old('city') }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Country</label>
                    <input type="text" name="country" class="form-control" value="{{ old('country') }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Currency</label>
                    @include('owner.settings.partials.currency-select', [
                        'fieldName' => 'currency',
                        'fieldValue' => old('currency', $defaultCurrency),
                        'required' => true,
                    ])
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Subscription Package</label>
                    <select name="package_id" class="form-control">
                        @foreach($packages as $package)
                            <option value="{{ $package->id }}" @selected(old('package_id') == $package->id)>
                                {{ $package->name }} — Install {{ $platformCurrency }} {{ number_format($package->installation_price, 2) }}, {{ $platformCurrency }} {{ number_format($package->yearly_price, 2) }}/yr
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Billing</label>
                    <input type="hidden" name="billing_cycle" value="yearly">
                    <input type="text" class="form-control" value="Yearly (installation + annual fee)" readonly>
                    <small class="text-muted">Churches pay a one-time installation fee, then renew yearly.</small>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input type="hidden" name="branches_enabled" value="0">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="branches_enabled" name="branches_enabled" value="1"
                            @checked(old('branches_enabled', false))>
                        <label class="custom-control-label" for="branches_enabled">Enable church branches</label>
                    </div>
                    <small class="text-muted">When enabled, the church dashboard shows branch management and branch filters. When disabled, the church operates as a single location.</small>
                </div>
            </div>
        </div>

        <hr>
        <h5 class="mb-3"><i class="fa fa-user"></i> Church Admin Login</h5>
        <p class="text-muted">A secure password will be generated automatically. You can copy and share it with the church after creation.</p>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Login Email (Username) *</label>
                    <input type="email" name="admin_email" id="admin_email" class="form-control"
                        value="{{ old('admin_email') }}" placeholder="Same as church email if left blank" required>
                    <small class="text-muted">Auto-fills from church email. Must be unique across the system.</small>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Create Church</button>
            <a href="{{ route('owner.churches.index') }}" class="btn btn-secondary">Cancel</a>
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
