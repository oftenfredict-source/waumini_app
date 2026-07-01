@extends('layouts.church')

@section('title', __('pages.expenses.record_expense'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-plus',
    'title' => __('pages.expenses.record_expense'),
    'subtitle' => __('pages.expenses.create_subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.budget_expenses'), 'route' => 'church.budget.index'],
        ['label' => __('pages.budget.expenses'), 'route' => 'church.expenses.index'],
        ['label' => __('pages.shared.breadcrumb_record')],
    ],
])

<div class="tile">
    <form method="POST" action="{{ route('church.expenses.store') }}">
        @csrf
        @include('church.budget.expenses._form', [
            'expense' => null,
            'budgets' => $budgets,
            'expenseCategories' => $expenseCategories,
            'paymentMethods' => $paymentMethods,
        ])
        <div class="tile-footer mt-3">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('pages.shared.save_item', ['item' => __('pages.expenses.item')]) }}</button>
            <a href="{{ route('church.expenses.index') }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
