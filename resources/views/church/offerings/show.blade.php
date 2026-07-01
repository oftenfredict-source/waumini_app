@extends('layouts.church')

@section('title', __('pages.offerings.show_title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-gift',
    'title' => __('pages.offerings.show_title'),
    'subtitle' => $offering->contributorLabel(),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.offerings'), 'route' => 'church.offerings.index'],
        ['label' => __('pages.shared.breadcrumb_details')],
    ],
])

<div class="row">
    <div class="col-md-8">
        <div class="tile">
            <h3 class="tile-title">{{ __('pages.shared.information', ['module' => __('pages.offerings.item')]) }}</h3>
            <table class="table table-borderless table-sm">
                <tr>
                    <th width="180">{{ __('common.status') }}</th>
                    <td>
                        <span class="badge badge-{{ $offering->approval_status->badgeClass() }}">
                            {{ $offering->approval_status->label() }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th width="180">{{ __('pages.shared.recorded_for') }}</th>
                    <td>
                        @if($offering->member)
                            <span class="badge badge-primary">{{ __('common.member') }}</span>
                            {{ $offering->member->full_name }}
                            @if($offering->member->envelope_number)
                                <small class="text-muted">({{ $offering->member->envelope_number }})</small>
                            @endif
                        @else
                            <span class="badge badge-info">{{ __('pages.offerings.general_offering') }}</span>
                            @if($offering->churchService)
                                {{ $offering->churchService->offeringSelectionLabel() }}
                            @else
                                {{ __('pages.offerings.show_not_linked_service') }}
                            @endif
                        @endif
                    </td>
                </tr>
                <tr><th>{{ __('pages.offerings.form_offering_type') }}</th><td>{{ $offering->offeringTypeLabel() }}</td></tr>
                <tr><th>{{ __('common.amount') }}</th><td><strong>TZS {{ number_format($offering->amount, 2) }}</strong></td></tr>
                <tr><th>{{ __('pages.offerings.form_offering_date') }}</th><td>{{ $offering->offering_date->format('M d, Y') }}</td></tr>
                <tr><th>{{ __('pages.shared.payment_method') }}</th><td>{{ $offering->payment_method?->label() ?? '—' }}</td></tr>
                <tr><th>{{ __('common.reference') }}</th><td>{{ $offering->reference_number ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.notes') }}</th><td>{{ $offering->notes ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.recorded_by') }}</th><td>{{ $offering->recorder?->name ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.recorded_on') }}</th><td>{{ $offering->created_at->format('M d, Y H:i') }}</td></tr>
                @if($offering->approved_at)
                    <tr><th>{{ __('pages.shared.approved_by') }}</th><td>{{ $offering->approver?->name ?? '—' }}</td></tr>
                    <tr><th>{{ __('pages.shared.approved_on') }}</th><td>{{ $offering->approved_at->format('M d, Y H:i') }}</td></tr>
                    @if($offering->approval_notes)
                        <tr><th>{{ __('pages.shared.approval_notes') }}</th><td>{{ $offering->approval_notes }}</td></tr>
                    @endif
                @endif
                @if($offering->rejection_reason)
                    <tr><th>{{ __('pages.shared.rejection_reason') }}</th><td class="text-danger">{{ $offering->rejection_reason }}</td></tr>
                @endif
            </table>
        </div>
    </div>
    <div class="col-md-4">
        <div class="tile">
            <h3 class="tile-title">{{ __('common.actions') }}</h3>
            <a href="{{ route('church.offerings.index') }}" class="btn btn-secondary btn-block">
                <i class="fa fa-arrow-left"></i> {{ __('pages.shared.back_to', ['module' => __('menu.offerings')]) }}
            </a>
            @can('update', $offering)
                <a href="{{ route('church.offerings.edit', $offering) }}" class="btn btn-primary btn-block mt-2">
                    <i class="fa fa-pencil"></i> {{ __('pages.shared.edit_item', ['item' => __('pages.offerings.item')]) }}
                </a>
            @endcan
            @if($offering->isPendingApproval() && auth()->user()->can('finance.approve'))
                <a href="{{ route('church.finance.approvals') }}" class="btn btn-success btn-block mt-2">
                    <i class="fa fa-check-circle"></i> {{ __('pages.shared.go_to_approvals') }}
                </a>
            @endif
            @can('delete', $offering)
                <form method="POST" action="{{ route('church.offerings.destroy', $offering) }}" class="mt-2"
                    data-swal-confirm="{{ __('pages.shared.delete_confirm', ['item' => __('pages.offerings.item')]) }}"
                    data-swal-delete
                    data-swal-confirm-text="{{ __('common.yes_delete') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block">
                        <i class="fa fa-trash"></i> {{ __('pages.shared.delete_item', ['item' => __('pages.offerings.item')]) }}
                    </button>
                </form>
            @endcan
        </div>
    </div>
</div>
@endsection
