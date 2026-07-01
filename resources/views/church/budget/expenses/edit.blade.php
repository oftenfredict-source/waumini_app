@extends('layouts.church')

@section('title', __('pages.shared.edit_item', ['item' => __('pages.expenses.item')]))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-pencil',
    'title' => __('pages.shared.edit_item', ['item' => __('pages.expenses.item')]),
    'subtitle' => __('pages.shared.update_subtitle', ['name' => $expense->expense_name]),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.budget_expenses'), 'route' => 'church.budget.index'],
        ['label' => __('pages.budget.expenses'), 'route' => 'church.expenses.index'],
        ['label' => __('pages.shared.breadcrumb_edit')],
    ],
])

<div class="tile">
    <form method="POST" action="{{ route('church.expenses.update', $expense) }}">
        @csrf
        @method('PUT')
        @include('church.budget.expenses._form', [
            'expense' => $expense,
            'budgets' => $budgets,
            'expenseCategories' => $expenseCategories,
            'paymentMethods' => $paymentMethods,
        ])
        <div class="tile-footer mt-3">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('pages.shared.update_item', ['item' => __('pages.expenses.item')]) }}</button>
            <a href="{{ route('church.expenses.show', $expense) }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
