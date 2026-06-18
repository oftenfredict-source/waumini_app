@extends('layouts.church')

@section('title', 'Edit SMS Template')

@section('content')
@include('church.system.partials.nav')

<div class="app-title">
    <div>
        <h1><i class="fa fa-pencil"></i> {{ $item['label'] }}</h1>
        <p>Edit SMS template for {{ $church->name }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.system.sms.index', ['tab' => 'templates']) }}">SMS Store</a></li>
        <li class="breadcrumb-item">Edit Template</li>
    </ul>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="tile">
            <form method="POST" action="{{ route('church.system.sms.templates.update', $item['key']) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Message Template <span class="text-danger">*</span></label>
                    <textarea name="body" rows="10" class="form-control @error('body') is-invalid @enderror" required maxlength="1000">{{ old('body', $item['body']) }}</textarea>
                    @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active"
                            @checked(old('is_active', $item['is_active']))>
                        <label class="form-check-label" for="is_active">Template is active</label>
                    </div>
                    <small class="text-muted">If inactive, the system default text is used instead.</small>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Save Template
                    </button>
                    <a href="{{ route('church.system.sms.index', ['tab' => 'templates']) }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <div class="col-md-4">
        <div class="tile">
            <h5 class="tile-title">Placeholders</h5>
            <p class="text-muted small">These are replaced automatically when the SMS is sent:</p>
            <ul class="list-unstyled mb-0">
                @foreach($item['placeholders'] as $placeholder)
                    <li><code>{{ $placeholder }}</code></li>
                @endforeach
            </ul>
        </div>
        @if($item['description'])
            <div class="tile">
                <h5 class="tile-title">About</h5>
                <p class="text-muted mb-0">{{ $item['description'] }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
