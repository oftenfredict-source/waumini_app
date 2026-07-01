@extends('layouts.church')

@section('title', __('pages.budget.create_title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-plus',
    'title' => __('pages.budget.create_title'),
    'subtitle' => __('pages.budget.create_subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.budget_expenses'), 'route' => 'church.budget.index'],
        ['label' => __('pages.shared.breadcrumb_create')],
    ],
])

<div class="tile">
    <form method="POST" action="{{ route('church.budget.store') }}">
        @csrf
        @include('church.budget._form', [
            'budget' => null,
            'budgetTypes' => $budgetTypes,
            'offeringTypes' => $offeringTypes,
            'statuses' => $statuses,
        ])
        <div class="tile-footer mt-3">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('pages.shared.save_item', ['item' => __('pages.budget.item')]) }}</button>
            <a href="{{ route('church.budget.index') }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
