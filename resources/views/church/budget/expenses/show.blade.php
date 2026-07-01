@extends('layouts.church')

@section('title', __('pages.expenses.show_title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-receipt',
    'title' => __('pages.expenses.show_title'),
    'subtitle' => $expense->expense_name,
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.budget_expenses'), 'route' => 'church.budget.index'],
        ['label' => __('pages.budget.expenses'), 'route' => 'church.expenses.index'],
        ['label' => __('pages.shared.breadcrumb_details')],
    ],
])

<div class="row">
    <div class="col-md-8">
        <div class="tile">
            <h3 class="tile-title">{{ __('pages.shared.information', ['module' => __('pages.expenses.item')]) }}</h3>
            <table class="table table-borderless table-sm">
                <tr>
                    <th width="180">{{ __('common.status') }}</th>
                    <td>
                        <span class="badge badge-{{ $expense->status->badgeClass() }}">
                            {{ $expense->status->label() }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>{{ __('pages.expenses.show_approval_status') }}</th>
                    <td>
                        <span class="badge badge-{{ $expense->approval_status->badgeClass() }}">
                            {{ $expense->approval_status->label() }}
                        </span>
                    </td>
                </tr>
                <tr><th>{{ __('pages.shared.budget') }}</th><td>{{ $expense->budget?->budget_name ?? '—' }}</td></tr>
                <tr><th>{{ __('common.category') }}</th><td><span class="badge badge-secondary">{{ $expense->expense_category->label() }}</span></td></tr>
                <tr><th>{{ __('common.amount') }}</th><td><strong>TZS {{ number_format($expense->amount, 2) }}</strong></td></tr>
                <tr><th>{{ __('common.date') }}</th><td>{{ $expense->expense_date->format('M d, Y') }}</td></tr>
                <tr><th>{{ __('pages.shared.payment_method') }}</th><td>{{ $expense->payment_method?->label() ?? '—' }}</td></tr>
                <tr><th>{{ __('common.reference') }}</th><td>{{ $expense->reference_number ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.vendor') }}</th><td>{{ $expense->vendor ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.receipt_number') }}</th><td>{{ $expense->receipt_number ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.notes') }}</th><td>{{ $expense->notes ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.recorded_by') }}</th><td>{{ $expense->recorder?->name ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.recorded_on') }}</th><td>{{ $expense->created_at->format('M d, Y H:i') }}</td></tr>
                @if($expense->approval_notes)
                    <tr><th>{{ __('pages.shared.approval_notes') }}</th><td>{{ $expense->approval_notes }}</td></tr>
                @endif
                @if($expense->rejection_reason)
                    <tr><th>{{ __('pages.shared.rejection_reason') }}</th><td class="text-danger">{{ $expense->rejection_reason }}</td></tr>
                @endif
                @if($expense->approved_at)
                    <tr><th>{{ __('pages.expenses.show_approved_at') }}</th><td>{{ $expense->approved_at?->format('M d, Y H:i') }}</td></tr>
                @endif
                @if($expense->paid_at)
                    <tr><th>{{ __('pages.expenses.show_paid_at') }}</th><td>{{ $expense->paid_at?->format('M d, Y H:i') }}</td></tr>
                @endif
            </table>
        </div>

        @can('markPaid', $expense)
            <div class="tile mt-3">
                <h3 class="tile-title">{{ __('pages.expenses.mark_as_paid') }}</h3>
                @if($expense->canBeMarkedPaid())
                    <p class="text-muted">{{ __('pages.expenses.mark_paid_hint') }}</p>
                <form method="POST" action="{{ route('church.budget.expenses.mark-paid', $expense) }}"
                        data-swal-confirm="{{ __('pages.expenses.mark_paid_confirm') }}">
                        @csrf
                        <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> {{ __('pages.expenses.mark_as_paid') }}</button>
                    </form>
                @else
                    <div class="alert alert-info mb-0">
                        {{ __('pages.expenses.cannot_mark_paid') }}
                    </div>
                @endif
            </div>
        @endcan
    </div>

    <div class="col-md-4">
        <div class="tile">
            <h3 class="tile-title">{{ __('common.actions') }}</h3>
            <a href="{{ route('church.expenses.index') }}" class="btn btn-secondary btn-block">
                <i class="fa fa-arrow-left"></i> {{ __('pages.shared.back_to', ['module' => __('pages.budget.expenses')]) }}
            </a>
            @can('update', $expense)
                <a href="{{ route('church.expenses.edit', $expense) }}" class="btn btn-primary btn-block mt-2">
                    <i class="fa fa-pencil"></i> {{ __('pages.shared.edit_item', ['item' => __('pages.expenses.item')]) }}
                </a>
            @endcan
            @can('delete', $expense)
                <form method="POST" action="{{ route('church.expenses.destroy', $expense) }}" class="mt-2"
                    data-swal-confirm="{{ __('pages.shared.delete_confirm', ['item' => __('pages.expenses.item')]) }}"
                    data-swal-delete
                    data-swal-confirm-text="{{ __('common.yes_delete') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block">
                        <i class="fa fa-trash"></i> {{ __('pages.shared.delete_item', ['item' => __('pages.expenses.item')]) }}
                    </button>
                </form>
            @endcan
            @if($expense->budget)
                <a href="{{ route('church.budget.show', $expense->budget) }}" class="btn btn-outline-primary btn-block mt-2">
                    <i class="fa fa-briefcase"></i> {{ __('pages.expenses.view_budget') }}
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
