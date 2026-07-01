@extends('layouts.owner')

@section('title', __('owner.church.edit_title'))

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-edit"></i> {{ __('owner.edit') }} {{ $church->name }}</h1>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('owner.churches.index') }}">{{ __('owner.churches') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('owner.churches.show', $church) }}">{{ $church->name }}</a></li>
        <li class="breadcrumb-item">{{ __('owner.edit') }}</li>
    </ul>
</div>

<div class="tile">
    <form method="POST" action="{{ route('owner.churches.update', $church) }}">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('owner.church.church_name') }} *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $church->name) }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('owner.church.subdomain') }}</label>
                    <input type="text" class="form-control" value="{{ $church->slug }}" disabled>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('owner.email') }} *</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $church->email) }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('owner.phone') }}</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $church->phone) }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('owner.church.pastor') }}</label>
                    <input type="text" name="pastor_name" class="form-control" value="{{ old('pastor_name', $church->pastor_name) }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('owner.church.denomination') }}</label>
                    <input type="text" name="denomination" class="form-control" value="{{ old('denomination', $church->denomination) }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>{{ __('owner.church.city') }}</label>
                    <input type="text" name="city" class="form-control" value="{{ old('city', $church->city) }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>{{ __('owner.church.country') }}</label>
                    <input type="text" name="country" class="form-control" value="{{ old('country', $church->country) }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>{{ __('owner.church.currency') }}</label>
                    @include('owner.settings.partials.currency-select', [
                        'fieldName' => 'currency',
                        'fieldValue' => old('currency', $church->currency),
                        'required' => true,
                    ])
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label>{{ __('owner.church.address') }}</label>
                    <textarea name="address" class="form-control" rows="2">{{ old('address', $church->address) }}</textarea>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input type="hidden" name="branches_enabled" value="0">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="branches_enabled" name="branches_enabled" value="1"
                            @checked(old('branches_enabled', $church->branches_enabled))>
                        <label class="custom-control-label" for="branches_enabled">{{ __('owner.church.enable_branches') }}</label>
                    </div>
                    <small class="text-muted">{{ __('owner.church.branches_help') }}</small>
                </div>
            </div>
        </div>
        <div class="mt-3">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('owner.church.save_changes') }}</button>
            <a href="{{ route('owner.churches.show', $church) }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
