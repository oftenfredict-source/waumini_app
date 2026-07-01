@extends('layouts.church')

@section('title', __('pages.tithes.show_title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-money',
    'title' => __('pages.tithes.show_title'),
    'subtitle' => $tithe->member?->full_name ?? __('pages.tithes.member_fallback'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.tithes'), 'route' => 'church.tithes.index'],
        ['label' => __('pages.shared.breadcrumb_details')],
    ],
])

<div class="row">
    <div class="col-md-8">
        <div class="tile">
            <h3 class="tile-title">{{ __('pages.shared.information', ['module' => __('pages.tithes.item')]) }}</h3>
            <table class="table table-borderless table-sm">
                <tr>
                    <th width="180">{{ __('common.status') }}</th>
                    <td>
                        <span class="badge badge-{{ $tithe->approval_status->badgeClass() }}">
                            {{ $tithe->approval_status->label() }}
                        </span>
                    </td>
                </tr>
                <tr><th>{{ __('common.member') }}</th><td>{{ $tithe->member?->full_name ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.envelope') }}</th><td>{{ $tithe->member?->envelope_number ?? '—' }}</td></tr>
                <tr><th>{{ __('common.amount') }}</th><td><strong>TZS {{ number_format($tithe->amount, 2) }}</strong></td></tr>
                <tr><th>{{ __('pages.tithes.form_tithe_date') }}</th><td>{{ $tithe->tithe_date->format('M d, Y') }}</td></tr>
                <tr><th>{{ __('pages.shared.payment_method') }}</th><td>{{ $tithe->payment_method?->label() ?? '—' }}</td></tr>
                <tr><th>{{ __('common.reference') }}</th><td>{{ $tithe->reference_number ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.notes') }}</th><td>{{ $tithe->notes ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.recorded_by') }}</th><td>{{ $tithe->recorder?->name ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.recorded_on') }}</th><td>{{ $tithe->created_at->format('M d, Y H:i') }}</td></tr>
                @if($tithe->approved_at)
                    <tr><th>{{ __('pages.shared.approved_by') }}</th><td>{{ $tithe->approver?->name ?? '—' }}</td></tr>
                    <tr><th>{{ __('pages.shared.approved_on') }}</th><td>{{ $tithe->approved_at->format('M d, Y H:i') }}</td></tr>
                    @if($tithe->approval_notes)
                        <tr><th>{{ __('pages.shared.approval_notes') }}</th><td>{{ $tithe->approval_notes }}</td></tr>
                    @endif
                @endif
                @if($tithe->rejection_reason)
                    <tr><th>{{ __('pages.shared.rejection_reason') }}</th><td class="text-danger">{{ $tithe->rejection_reason }}</td></tr>
                @endif
            </table>
        </div>
    </div>
    <div class="col-md-4">
        <div class="tile">
            <h3 class="tile-title">{{ __('common.actions') }}</h3>
            <a href="{{ route('church.tithes.index') }}" class="btn btn-secondary btn-block">
                <i class="fa fa-arrow-left"></i> {{ __('pages.shared.back_to', ['module' => __('menu.tithes')]) }}
            </a>
            @can('update', $tithe)
                <a href="{{ route('church.tithes.edit', $tithe) }}" class="btn btn-primary btn-block mt-2">
                    <i class="fa fa-pencil"></i> {{ __('pages.shared.edit_item', ['item' => __('pages.tithes.item')]) }}
                </a>
            @endcan
            @if($tithe->isPendingApproval() && auth()->user()->can('finance.approve'))
                <a href="{{ route('church.finance.approvals') }}" class="btn btn-success btn-block mt-2">
                    <i class="fa fa-check-circle"></i> {{ __('pages.shared.go_to_approvals') }}
                </a>
            @endif
            @can('delete', $tithe)
                <form method="POST" action="{{ route('church.tithes.destroy', $tithe) }}" class="mt-2"
                    data-swal-confirm="{{ __('pages.shared.delete_confirm', ['item' => __('pages.tithes.item')]) }}"
                    data-swal-delete
                    data-swal-confirm-text="{{ __('common.yes_delete') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block">
                        <i class="fa fa-trash"></i> {{ __('pages.shared.delete_item', ['item' => __('pages.tithes.item')]) }}
                    </button>
                </form>
            @endcan
        </div>
    </div>
</div>
@endsection
