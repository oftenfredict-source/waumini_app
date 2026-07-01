@extends('layouts.church')

@section('title', $event->deceased_name)

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-heart',
    'title' => $event->deceased_name,
    'subtitle' => __('pages.shared.incident_on', ['date' => $event->incident_date->format('M d, Y')]),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.bereavements'), 'route' => 'church.bereavements.index'],
        ['label' => $event->deceased_name],
    ],
])

<div class="row mb-3">
    <div class="col-md-3">
        <div class="widget-small primary coloured-icon">
            <i class="icon fa fa-money fa-3x"></i>
            <div class="info">
                <h4>TZS {{ number_format($totalContributions, 2) }}</h4>
                <p>{{ __('pages.shared.total_raised') }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small info coloured-icon">
            <i class="icon fa fa-users fa-3x"></i>
            <div class="info">
                <h4>{{ $contributorsCount }}</h4>
                <p>{{ __('pages.shared.contributors') }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small warning coloured-icon">
            <i class="icon fa fa-clock-o fa-3x"></i>
            <div class="info">
                <h4>{{ $pendingCount }}</h4>
                <p>{{ __('common.pending') }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small {{ $event->isOpen() ? 'success' : 'secondary' }} coloured-icon">
            <i class="icon fa fa-calendar fa-3x"></i>
            <div class="info">
                <h4>{{ $event->isOpen() ? $daysRemaining : '—' }}</h4>
                <p>{{ $event->isOpen() ? __('pages.shared.days_remaining') : __('pages.shared.closed') }}</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="tile">
            <h3 class="tile-title">{{ __('pages.shared.event_details') }}</h3>
            <table class="table table-borderless table-sm">
                <tr>
                    <th width="180">{{ __('common.status') }}</th>
                    <td>
                        <span class="badge badge-{{ $event->status->badgeClass() }}">{{ $event->status->label() }}</span>
                        @if($event->isExpired())
                            <span class="badge badge-warning">{{ __('pages.bereavements.contribution_period_ended') }}</span>
                        @endif
                    </td>
                </tr>
                <tr><th>{{ __('pages.shared.incident_date') }}</th><td>{{ $event->incident_date->format('M d, Y') }}</td></tr>
                <tr>
                    <th>{{ __('pages.shared.contribution_period') }}</th>
                    <td>
                        {{ $event->contribution_start_date->format('M d, Y') }}
                        – {{ $event->contribution_end_date->format('M d, Y') }}
                    </td>
                </tr>
                @if($event->affectedMember)
                    <tr><th>{{ __('pages.shared.related_member') }}</th><td>{{ $event->affectedMember->full_name }}</td></tr>
                @endif
                @if($event->family_details)
                    <tr><th>{{ __('pages.shared.family_details') }}</th><td>{{ $event->family_details }}</td></tr>
                @endif
                @if($event->related_departments)
                    <tr><th>{{ __('pages.shared.departments') }}</th><td>{{ $event->related_departments }}</td></tr>
                @endif
                @if($event->notes)
                    <tr><th>{{ __('pages.shared.notes') }}</th><td>{{ $event->notes }}</td></tr>
                @endif
                @if($event->fund_usage)
                    <tr><th>{{ __('pages.shared.fund_usage') }}</th><td>{{ $event->fund_usage }}</td></tr>
                @endif
                <tr><th>{{ __('pages.shared.created_by') }}</th><td>{{ $event->creator?->name ?? '—' }}</td></tr>
                @if($event->closed_at)
                    <tr><th>{{ __('pages.shared.closed_on') }}</th><td>{{ $event->closed_at->format('M d, Y H:i') }}</td></tr>
                @endif
            </table>
        </div>

        @can('manageContributions', $event)
            <div class="tile">
                <h3 class="tile-title">{{ __('pages.bereavements.record_contribution') }}</h3>
                <form method="POST" action="{{ route('church.bereavements.contribution', $event) }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('common.member') }} *</label>
                                <select name="member_id" class="form-control @error('member_id') is-invalid @enderror" required>
                                    <option value="">{{ __('pages.shared.select_member') }}</option>
                                    @foreach($availableMembers as $member)
                                        <option value="{{ $member->id }}" @selected(old('member_id') == $member->id)>
                                            {{ $member->full_name }}@if($member->envelope_number) ({{ $member->envelope_number }})@endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('member_id')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __('pages.shared.amount_tzs') }} *</label>
                                <input type="number" step="0.01" min="0" name="amount"
                                    class="form-control @error('amount') is-invalid @enderror"
                                    value="{{ old('amount') }}" required>
                                @error('amount')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __('common.date') }} *</label>
                                <input type="date" name="contribution_date"
                                    class="form-control @error('contribution_date') is-invalid @enderror"
                                    value="{{ old('contribution_date', now()->toDateString()) }}" required>
                                @error('contribution_date')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ __('common.type') }} *</label>
                                <select name="contribution_type" class="form-control @error('contribution_type') is-invalid @enderror" required>
                                    @foreach($contributionTypes as $type)
                                        <option value="{{ $type->value }}" @selected(old('contribution_type') === $type->value)>
                                            {{ $type->label() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('contribution_type')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ __('pages.shared.payment_method') }} *</label>
                                <select name="payment_method" id="payment_method" class="form-control @error('payment_method') is-invalid @enderror" required>
                                    @foreach($paymentMethods as $method)
                                        <option value="{{ $method->value }}" @selected(old('payment_method') === $method->value)>
                                            {{ $method->label() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('payment_method')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="col-md-4" id="referenceGroup">
                            <div class="form-group">
                                <label>{{ __('pages.shared.reference_number') }}</label>
                                <input type="text" name="reference_number"
                                    class="form-control @error('reference_number') is-invalid @enderror"
                                    value="{{ old('reference_number') }}">
                                @error('reference_number')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>{{ __('pages.shared.notes') }}</label>
                                <input type="text" name="notes" class="form-control @error('notes') is-invalid @enderror"
                                    value="{{ old('notes') }}">
                                @error('notes')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> {{ __('pages.bereavements.record_contribution') }}</button>
                </form>
            </div>
        @endcan

        <div class="tile">
            <h3 class="tile-title">{{ __('pages.bereavements.contributors_heading', ['count' => $contributors->count()]) }}</h3>
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>{{ __('common.member') }}</th>
                            <th>{{ __('common.amount') }}</th>
                            <th>{{ __('common.date') }}</th>
                            <th>{{ __('common.type') }}</th>
                            <th>{{ __('pages.shared.payment') }}</th>
                            <th>{{ __('common.reference') }}</th>
                            @can('manageContributions', $event)
                                <th width="80"></th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contributors as $contribution)
                            <tr>
                                <td>{{ $contribution->member?->full_name ?? '—' }}</td>
                                <td>TZS {{ number_format($contribution->amount ?? 0, 2) }}</td>
                                <td>{{ $contribution->contribution_date?->format('M d, Y') ?? '—' }}</td>
                                <td>{{ $contribution->contribution_type?->label() ?? '—' }}</td>
                                <td>{{ $contribution->payment_method?->label() ?? '—' }}</td>
                                <td>{{ $contribution->reference_number ?? '—' }}</td>
                                @can('manageContributions', $event)
                                    <td>
                                        <form method="POST" action="{{ route('church.bereavements.non-contributor', $event) }}" class="d-inline"
                                            data-swal-confirm="{{ __('pages.bereavements.mark_pending_confirm') }}">
                                            @csrf
                                            <input type="hidden" name="member_id" value="{{ $contribution->member_id }}">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="{{ __('pages.bereavements.mark_pending_title') }}">
                                                <i class="fa fa-undo"></i>
                                            </button>
                                        </form>
                                    </td>
                                @endcan
                            </tr>
                        @empty
                            <tr><td colspan="{{ auth()->user()->can('manageContributions', $event) ? 7 : 6 }}" class="text-center text-muted">{{ __('pages.bereavements.no_contributions') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tile">
            <h3 class="tile-title">{{ __('pages.bereavements.pending_members', ['count' => $pending->count()]) }}</h3>
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>{{ __('common.member') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pending as $contribution)
                            <tr>
                                <td>{{ $contribution->member?->full_name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center text-muted">
                                    {{ __('pages.bereavements.all_contributed') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="tile">
            <h3 class="tile-title">{{ __('common.actions') }}</h3>
            <a href="{{ route('church.bereavements.index') }}" class="btn btn-secondary btn-block">
                <i class="fa fa-arrow-left"></i> {{ __('pages.shared.back_to_list') }}
            </a>
            @can('update', $event)
                <a href="{{ route('church.bereavements.edit', $event) }}" class="btn btn-primary btn-block mt-2">
                    <i class="fa fa-pencil"></i> {{ __('pages.bereavements.edit_event') }}
                </a>
            @endcan
            @can('close', $event)
                <hr>
                <form method="POST" action="{{ route('church.bereavements.close', $event) }}"
                    data-swal-confirm="{{ __('pages.bereavements.close_confirm') }}">
                    @csrf
                    <div class="form-group">
                        <label>{{ __('pages.bereavements.fund_usage_optional') }}</label>
                        <textarea name="fund_usage" rows="3" class="form-control"
                            placeholder="{{ __('pages.bereavements.fund_usage_placeholder') }}">{{ old('fund_usage', $event->fund_usage) }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-warning btn-block">
                        <i class="fa fa-lock"></i> {{ __('pages.bereavements.close_event') }}
                    </button>
                </form>
            @endcan
            @can('delete', $event)
                <form method="POST" action="{{ route('church.bereavements.destroy', $event) }}" class="mt-2"
                    data-swal-confirm="{{ __('pages.shared.delete_confirm_named', ['name' => $event->deceased_name]) }}"
                    data-swal-delete
                    data-swal-confirm-text="{{ __('common.yes_delete') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block">
                        <i class="fa fa-trash"></i> {{ __('pages.bereavements.delete_event') }}
                    </button>
                </form>
            @endcan
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const paymentSelect = document.getElementById('payment_method');
    const referenceGroup = document.getElementById('referenceGroup');
    if (!paymentSelect || !referenceGroup) return;

    function toggleReference() {
        const needsRef = ['bank_transfer', 'mobile_money'].includes(paymentSelect.value);
        referenceGroup.style.display = needsRef ? '' : 'none';
    }

    paymentSelect.addEventListener('change', toggleReference);
    toggleReference();
});
</script>
@endpush
