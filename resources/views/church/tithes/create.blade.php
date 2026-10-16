@extends('layouts.church')

@section('title', __('pages.tithes.record_tithe'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-plus',
    'title' => __('pages.tithes.record_tithe'),
    'subtitle' => __('pages.tithes.create_subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.tithes'), 'route' => 'church.tithes.index'],
        ['label' => __('pages.shared.breadcrumb_record')],
    ],
])

<div class="tile">
    <form method="POST" action="{{ route('church.tithes.store') }}">
        @csrf
        @include('church.tithes._bulk-form', [
            'members' => $members,
            'paymentMethods' => $paymentMethods,
        ])
        <div class="tile-footer mt-3">
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-save"></i> {{ __('pages.tithes.save_all') }}
            </button>
            <a href="{{ route('church.tithes.index') }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
@include('church.tithes._bulk-form-scripts')
@endpush
