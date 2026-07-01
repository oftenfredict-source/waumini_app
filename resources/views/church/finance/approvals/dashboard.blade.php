@extends('layouts.church')

@section('title', __('finance.approvals_title'))

@push('styles')
<style>
    .approval-hero {
        background: linear-gradient(135deg, #940000 0%, #600000 100%);
        border-radius: 8px;
        color: #fff;
        padding: 1.5rem 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 8px 24px rgba(148, 0, 0, 0.18);
    }
    .approval-hero h2 { color: #fff; margin-bottom: 0.35rem; }
    .approval-stat {
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.06);
        height: 100%;
    }
    .approval-stat .stat-value {
        font-size: 1.6rem;
        font-weight: 700;
        line-height: 1.2;
    }
    .approval-stat .stat-label {
        color: #6c757d;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .approval-tabs .nav-link {
        border-radius: 999px;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
        color: #495057;
        border: 1px solid #e9ecef;
    }
    .approval-tabs .nav-link.active {
        background: #940000;
        border-color: #940000;
        color: #fff;
    }
    .approval-tabs .badge {
        background: rgba(255,255,255,0.25);
        color: inherit;
    }
    .approval-tabs .nav-link:not(.active) .badge {
        background: #ffc107;
        color: #212529;
    }
</style>
@endpush

@section('content')
@php
    $summary = $dashboard;
@endphp

<div class="approval-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h2><i class="fa fa-check-circle"></i> {{ __('finance.approvals_heading') }}</h2>
            <p class="mb-0" style="color:rgba(255,255,255,0.85);">{{ __('finance.approvals_subtitle') }}</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('church.finance.dashboard') }}" class="btn btn-light btn-sm">
                <i class="fa fa-line-chart"></i> {{ __('finance.finance_dashboard') }}
            </a>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card approval-stat">
            <div class="card-body">
                <div class="stat-label">{{ __('finance.pending_records') }}</div>
                <div class="stat-value text-warning">{{ $summary['total_pending'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card approval-stat">
            <div class="card-body">
                <div class="stat-label">{{ __('finance.pending_amount') }}</div>
                <div class="stat-value text-primary">TZS {{ number_format($summary['total_pending_amount'], 0) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card approval-stat">
            <div class="card-body">
                <div class="stat-label">{{ __('finance.todays_date') }}</div>
                <div class="stat-value">{{ now()->format('M d, Y') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card approval-stat">
            <div class="card-body">
                <div class="stat-label">{{ __('finance.approver') }}</div>
                <div class="stat-value" style="font-size:1.1rem;">{{ auth()->user()->name }}</div>
            </div>
        </div>
    </div>
</div>

@if($canApprove)
<div class="row mb-3">
    <div class="col-md-12">
        <div class="tile">
            <div class="d-flex flex-wrap align-items-center">
                <button type="button" class="btn btn-success mr-2 mb-2" id="bulkApproveBtn">
                    <i class="fa fa-check-double"></i> {{ __('finance.bulk_approve') }}
                </button>
                <button type="button" class="btn btn-primary mr-2 mb-2" onclick="window.location.reload()">
                    <i class="fa fa-refresh"></i> {{ __('finance.refresh') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<div class="tile">
    <h3 class="tile-title"><i class="fa fa-list"></i> {{ __('finance.pending_financial_records') }}</h3>

    <ul class="nav nav-pills approval-tabs mb-3" role="tablist">
        @foreach($types as $key => $meta)
            <li class="nav-item">
                <a class="nav-link {{ $loop->first ? 'active' : '' }}" data-toggle="tab" href="#tab-{{ $key }}" role="tab">
                    <i class="fa {{ $meta['icon'] }}"></i> {{ $meta['label'] }}
                    <span class="badge ml-1">{{ $summary['counts'][$key] ?? 0 }}</span>
                </a>
            </li>
        @endforeach
        @foreach($summary['placeholder_tabs'] as $tab)
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tab-{{ $tab['key'] }}" role="tab">
                    <i class="fa {{ $tab['icon'] }}"></i> {{ $tab['label'] }}
                    <span class="badge ml-1">{{ $tab['count'] }}</span>
                </a>
            </li>
        @endforeach
    </ul>

    <div class="tab-content">
        @foreach($types as $key => $meta)
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="tab-{{ $key }}" role="tabpanel">
                @include('church.finance.approvals.partials.records-table', [
                    'records' => $summary['pending'][$key],
                    'type' => $key,
                    'label' => $meta['label'],
                    'dateField' => $meta['date_field'],
                    'canApprove' => $canApprove,
                ])
            </div>
        @endforeach
        @foreach($summary['placeholder_tabs'] as $tab)
            <div class="tab-pane fade" id="tab-{{ $tab['key'] }}" role="tabpanel">
                <div class="text-center text-muted py-5">
                    <i class="fa {{ $tab['icon'] }} fa-3x mb-3"></i>
                    <h5>{{ __('finance.tab_approvals', ['label' => $tab['label']]) }}</h5>
                    <p class="mb-0">{{ __('finance.tab_placeholder', ['module' => strtolower($tab['label'])]) }}</p>
                </div>
            </div>
        @endforeach
    </div>
</div>

@if($summary['recent_approvals']->isNotEmpty())
<div class="tile mt-3">
    <h3 class="tile-title"><i class="fa fa-history"></i> {{ __('finance.recent_approvals') }}</h3>
    <div class="table-responsive">
        <table class="table table-hover table-bordered table-sm">
            <thead>
                <tr>
                    <th>{{ __('common.type') }}</th>
                    <th>{{ __('common.member') }}</th>
                    <th>{{ __('common.amount') }}</th>
                    <th>{{ __('common.date') }}</th>
                    <th>{{ __('finance.approved_by') }}</th>
                    <th>{{ __('finance.approved_at') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($summary['recent_approvals'] as $record)
                    <tr>
                        <td><span class="badge badge-primary">{{ $record['type'] }}</span></td>
                        <td>{{ $record['member_name'] }}</td>
                        <td>TZS {{ number_format($record['amount'], 0) }}</td>
                        <td>{{ $record['date']?->format('M d, Y') ?? '—' }}</td>
                        <td>{{ $record['approved_by'] }}</td>
                        <td>{{ $record['approved_at']?->format('M d, Y H:i') ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="modal fade" id="approvalModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('church.finance.approvals.approve') }}" id="approvalForm">
                @csrf
                <input type="hidden" name="type" id="approvalType">
                <input type="hidden" name="id" id="approvalId">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('finance.approve_record') }}</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ __('finance.approval_notes') }}</label>
                        <textarea name="approval_notes" class="form-control" rows="3" placeholder="{{ __('finance.approval_notes_placeholder') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> {{ __('finance.approve') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('church.finance.approvals.reject') }}" id="rejectionForm">
                @csrf
                <input type="hidden" name="type" id="rejectionType">
                <input type="hidden" name="id" id="rejectionId">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('finance.reject_record') }}</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ __('finance.rejection_reason') }}</label>
                        <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="{{ __('finance.rejection_reason_placeholder') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-danger"><i class="fa fa-times"></i> {{ __('finance.reject') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="detailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('finance.record_details') }}</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="detailsModalBody">
                <div class="text-center text-muted py-4"><i class="fa fa-spinner fa-spin"></i> {{ __('common.loading') }}</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('common.close') }}</button>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('church.finance.approvals.bulk-approve') }}" id="bulkApproveForm" class="d-none">
    @csrf
    <input type="hidden" name="approval_notes" id="bulkApprovalNotes" value="">
</form>
@endsection

@push('scripts')
@php
    $financeJs = [
        'loading' => __('common.loading'),
        'failed' => __('pages.system_sms.failed'),
        'failed_load' => __('finance.failed_load'),
        'type' => __('common.type'),
        'amount' => __('common.amount'),
        'date' => __('common.date'),
        'status' => __('common.status'),
        'member' => __('common.member'),
        'payment' => __('finance.payment'),
        'reference' => __('common.reference'),
        'recorded_by' => __('pages.shared.recorded_by'),
        'offering_type' => __('finance.offering_type'),
        'pledge_type' => __('finance.pledge_type'),
        'pledged_amount' => __('finance.pledged_amount'),
        'paid_amount' => __('finance.paid_amount'),
        'budget_type' => __('finance.budget_type'),
        'budget_status' => __('finance.budget_status'),
        'purpose' => __('finance.purpose'),
        'primary_offering' => __('finance.primary_offering'),
        'expense_category' => __('finance.expense_category'),
        'vendor' => __('finance.vendor'),
        'receipt' => __('finance.receipt'),
        'notes' => __('finance.notes'),
        'no_records_selected' => __('finance.no_records_selected'),
        'select_at_least_one' => __('finance.select_at_least_one'),
        'approve_selected' => __('finance.approve_selected'),
        'approve_count' => __('finance.approve_count', ['count' => ':count']),
        'yes_approve' => __('finance.yes_approve'),
        'cancel' => __('common.cancel'),
        'bulk_approved_by' => __('finance.bulk_approved_by', ['name' => auth()->user()->name]),
    ];
@endphp
<script>
document.addEventListener('DOMContentLoaded', function () {
    const i18n = @json($financeJs);
    const hash = window.location.hash;
    if (hash && hash.startsWith('#tab-')) {
        const tabLink = document.querySelector(`a[href="${hash}"]`);
        if (tabLink && typeof $(tabLink).tab === 'function') {
            $(tabLink).tab('show');
        }
    }

    document.querySelectorAll('.btn-approve-record').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('approvalType').value = btn.dataset.type;
            document.getElementById('approvalId').value = btn.dataset.id;
            $('#approvalModal').modal('show');
        });
    });

    document.querySelectorAll('.btn-reject-record').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('rejectionType').value = btn.dataset.type;
            document.getElementById('rejectionId').value = btn.dataset.id;
            $('#rejectionModal').modal('show');
        });
    });

    document.querySelectorAll('.btn-view-record').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const type = btn.dataset.type;
            const id = btn.dataset.id;
            const body = document.getElementById('detailsModalBody');
            body.innerHTML = '<div class="text-center text-muted py-4"><i class="fa fa-spinner fa-spin"></i> ' + i18n.loading + '</div>';
            $('#detailsModal').modal('show');

            fetch(`{{ url('finance/approvals/details') }}/${type}/${id}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => r.json())
                .then(res => {
                    if (!res.success) throw new Error(res.message || i18n.failed);
                    const d = res.data;
                    body.innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>${i18n.type}:</strong> ${d.type}</p>
                                <p><strong>${i18n.amount}:</strong> TZS ${Number(d.amount).toLocaleString()}</p>
                                <p><strong>${i18n.date}:</strong> ${d.date || '—'}</p>
                                <p><strong>${i18n.status}:</strong> <span class="badge badge-warning">${d.approval_status}</span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>${i18n.member}:</strong> ${d.member_name || '—'}</p>
                                <p><strong>${i18n.payment}:</strong> ${d.payment_method || '—'}</p>
                                <p><strong>${i18n.reference}:</strong> ${d.reference_number || '—'}</p>
                                <p><strong>${i18n.recorded_by}:</strong> ${d.recorded_by || '—'}</p>
                            </div>
                        </div>
                        ${d.offering_type ? `<p><strong>${i18n.offering_type}:</strong> ${d.offering_type}</p>` : ''}
                        ${d.pledge_type ? `<p><strong>${i18n.pledge_type}:</strong> ${d.pledge_type}</p>` : ''}
                        ${d.pledge_amount != null ? `<p><strong>${i18n.pledged_amount}:</strong> TZS ${Number(d.pledge_amount).toLocaleString()}</p>` : ''}
                        ${d.pledge_paid != null ? `<p><strong>${i18n.paid_amount}:</strong> TZS ${Number(d.pledge_paid).toLocaleString()}</p>` : ''}
                        ${d.budget_type ? `<p><strong>${i18n.budget_type}:</strong> ${d.budget_type}</p>` : ''}
                        ${d.budget_status ? `<p><strong>${i18n.budget_status}:</strong> ${d.budget_status}</p>` : ''}
                        ${d.purpose ? `<p><strong>${i18n.purpose}:</strong> ${d.purpose}</p>` : ''}
                        ${d.primary_offering_type ? `<p><strong>${i18n.primary_offering}:</strong> ${d.primary_offering_type}</p>` : ''}
                        ${d.expense_category ? `<p><strong>${i18n.expense_category}:</strong> ${d.expense_category}</p>` : ''}
                        ${d.vendor ? `<p><strong>${i18n.vendor}:</strong> ${d.vendor}</p>` : ''}
                        ${d.receipt_number ? `<p><strong>${i18n.receipt}:</strong> ${d.receipt_number}</p>` : ''}
                        ${d.notes ? `<hr><p><strong>${i18n.notes}:</strong><br>${d.notes}</p>` : ''}
                    `;
                })
                .catch(() => {
                    body.innerHTML = '<div class="alert alert-danger mb-0">' + i18n.failed_load + '</div>';
                });
        });
    });

    document.querySelectorAll('.select-all-pending').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            const type = checkbox.dataset.type;
            document.querySelectorAll(`.pending-record-checkbox[data-type="${type}"]`).forEach(function (item) {
                item.checked = checkbox.checked;
            });
        });
    });

    const bulkBtn = document.getElementById('bulkApproveBtn');
    if (bulkBtn) {
        bulkBtn.addEventListener('click', function () {
            const selected = document.querySelectorAll('.pending-record-checkbox:checked');
            if (selected.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: i18n.no_records_selected,
                    text: i18n.select_at_least_one,
                    confirmButtonColor: document.querySelector('meta[name="brand-color"]')?.content || '#940000',
                });
                return;
            }

            Swal.fire({
                title: i18n.approve_selected,
                text: i18n.approve_count.replace(':count', selected.length),
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: document.querySelector('meta[name="brand-color"]')?.content || '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: i18n.yes_approve,
                cancelButtonText: i18n.cancel,
                reverseButtons: true,
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }

                const form = document.getElementById('bulkApproveForm');
                form.querySelectorAll('input[name^="records"]').forEach(el => el.remove());

                selected.forEach(function (checkbox, index) {
                    const typeInput = document.createElement('input');
                    typeInput.type = 'hidden';
                    typeInput.name = `records[${index}][type]`;
                    typeInput.value = checkbox.dataset.type;
                    form.appendChild(typeInput);

                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = `records[${index}][id]`;
                    idInput.value = checkbox.value;
                    form.appendChild(idInput);
                });

                document.getElementById('bulkApprovalNotes').value = i18n.bulk_approved_by;
                form.submit();
            });
        });
    }
});
</script>
@endpush
