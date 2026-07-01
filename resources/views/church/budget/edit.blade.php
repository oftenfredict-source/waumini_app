@extends('layouts.church')

@section('title', __('pages.shared.edit_item', ['item' => __('pages.budget.item')]))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-pencil',
    'title' => __('pages.shared.edit_item', ['item' => __('pages.budget.item')]),
    'subtitle' => __('pages.shared.update_subtitle', ['name' => $budget->budget_name]),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.budget_expenses'), 'route' => 'church.budget.index'],
        ['label' => __('pages.shared.breadcrumb_edit')],
    ],
])

<div class="tile">
    <form method="POST" action="{{ route('church.budget.update', $budget) }}">
        @csrf
        @method('PUT')
        @include('church.budget._form', [
            'budget' => $budget,
            'budgetTypes' => $budgetTypes,
            'offeringTypes' => $offeringTypes,
            'statuses' => $statuses,
            'existingAllocations' => $existingAllocations,
        ])
        <div class="tile-footer mt-3">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('pages.shared.update_item', ['item' => __('pages.budget.item')]) }}</button>
            <a href="{{ route('church.budget.show', $budget) }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
