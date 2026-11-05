@extends('layouts.church')

@section('title', __('pages.support.create_title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-plus',
    'title' => __('pages.support.create_title'),
    'subtitle' => __('pages.support.create_subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('pages.support.title'), 'route' => 'church.system.support.index'],
        ['label' => __('pages.support.create')],
    ],
])

<div class="tile">
    <form method="POST" action="{{ route('church.system.support.store') }}">
        @csrf
        <div class="row">
            <div class="col-md-8">
                <div class="form-group">
                    <label>{{ __('pages.support.subject') }} *</label>
                    <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror"
                        value="{{ old('subject') }}" maxlength="200" required>
                    @error('subject')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>{{ __('pages.support.priority') }} *</label>
                    <select name="priority" class="form-control @error('priority') is-invalid @enderror" required>
                        @foreach(['low','medium','high','urgent'] as $priority)
                            <option value="{{ $priority }}" @selected(old('priority', 'medium') === $priority)>
                                {{ __('pages.support.priorities.'.$priority) }}
                            </option>
                        @endforeach
                    </select>
                    @error('priority')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>{{ __('pages.support.category') }} *</label>
                    <select name="category" class="form-control @error('category') is-invalid @enderror" required>
                        @foreach(['general','billing','technical','account','other'] as $category)
                            <option value="{{ $category }}" @selected(old('category', 'general') === $category)>
                                {{ __('pages.support.categories.'.$category) }}
                            </option>
                        @endforeach
                    </select>
                    @error('category')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label>{{ __('pages.support.message') }} *</label>
                    <textarea name="body" rows="8" class="form-control @error('body') is-invalid @enderror" required>{{ old('body') }}</textarea>
                    <small class="text-muted">{{ __('pages.support.message_hint') }}</small>
                    @error('body')<small class="text-danger d-block">{{ $message }}</small>@enderror
                </div>
            </div>
        </div>
        <div class="mt-2">
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-paper-plane"></i> {{ __('pages.support.submit') }}
            </button>
            <a href="{{ route('church.system.support.index') }}" class="btn btn-outline-secondary">{{ __('common.back') }}</a>
        </div>
    </form>
</div>
@endsection
