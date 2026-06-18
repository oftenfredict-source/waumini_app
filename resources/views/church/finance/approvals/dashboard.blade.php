@extends('layouts.church')

@section('title', 'Approval Dashboard')

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
            <h2><i class="fa fa-check-circle"></i> Financial Approval Dashboard</h2>
            <p class="mb-0" style="color:rgba(255,255,255,0.85);">Review and approve pending tithes, offerings, and other financial records.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('church.finance.dashboard') }}" class="btn btn-light btn-sm">
                <i class="fa fa-line-chart"></i> Finance Dashboard
            </a>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card approval-stat">
            <div class="card-body">
                <div class="stat-label">Pending Records</div>
                <div class="stat-value text-warning">{{ $summary['total_pending'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card approval-stat">
            <div class="card-body">
                <div class="stat-label">Pending Amount</div>
                <div class="stat-value text-primary">TZS {{ number_format($summary['total_pending_amount'], 0) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card approval-stat">
            <div class="card-body">
                <div class="stat-label">Today's Date</div>
                <div class="stat-value">{{ now()->format('M d, Y') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card approval-stat">
            <div class="card-body">
                <div class="stat-label">Approver</div>
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
                    <i class="fa fa-check-double"></i> Bulk Approve Selected
                </button>
                <button type="button" class="btn btn-primary mr-2 mb-2" onclick="window.location.reload()">
                    <i class="fa fa-refresh"></i> Refresh
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<div class="tile">
    <h3 class="tile-title"><i class="fa fa-list"></i> Pending Financial Records</h3>

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
                    <h5>{{ $tab['label'] }} approvals</h5>
                    <p class="mb-0">This section will activate when the {{ strtolower($tab['label']) }} module is implemented.</p>
                </div>
            </div>
        @endforeach
    </div>
</div>

@if($summary['recent_approvals']->isNotEmpty())
<div class="tile mt-3">
    <h3 class="tile-title"><i class="fa fa-history"></i> Recent Approvals (Last 7 Days)</h3>
    <div class="table-responsive">
        <table class="table table-hover table-bordered table-sm">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Member</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Approved By</th>
                    <th>Approved At</th>
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
                    <h5 class="modal-title">Approve Record</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Approval Notes (optional)</label>
                        <textarea name="approval_notes" class="form-control" rows="3" placeholder="Add any notes about this approval..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Approve</button>
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
                    <h5 class="modal-title">Reject Record</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Rejection Reason *</label>
                        <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Please provide a reason for rejection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="fa fa-times"></i> Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="detailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record Details</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="detailsModalBody">
                <div class="text-center text-muted py-4"><i class="fa fa-spinner fa-spin"></i> Loading...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
<script>
document.addEventListener('DOMContentLoaded', function () {
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
            body.innerHTML = '<div class="text-center text-muted py-4"><i class="fa fa-spinner fa-spin"></i> Loading...</div>';
            $('#detailsModal').modal('show');

            fetch(`{{ url('finance/approvals/details') }}/${type}/${id}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => r.json())
                .then(res => {
                    if (!res.success) throw new Error(res.message || 'Failed');
                    const d = res.data;
                    body.innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Type:</strong> ${d.type}</p>
                                <p><strong>Amount:</strong> TZS ${Number(d.amount).toLocaleString()}</p>
                                <p><strong>Date:</strong> ${d.date || '—'}</p>
                                <p><strong>Status:</strong> <span class="badge badge-warning">${d.approval_status}</span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Member:</strong> ${d.member_name || '—'}</p>
                                <p><strong>Payment:</strong> ${d.payment_method || '—'}</p>
                                <p><strong>Reference:</strong> ${d.reference_number || '—'}</p>
                                <p><strong>Recorded By:</strong> ${d.recorded_by || '—'}</p>
                            </div>
                        </div>
                        ${d.offering_type ? `<p><strong>Offering Type:</strong> ${d.offering_type}</p>` : ''}
                        ${d.pledge_type ? `<p><strong>Pledge Type:</strong> ${d.pledge_type}</p>` : ''}
                        ${d.pledge_amount != null ? `<p><strong>Pledged Amount:</strong> TZS ${Number(d.pledge_amount).toLocaleString()}</p>` : ''}
                        ${d.pledge_paid != null ? `<p><strong>Paid Amount:</strong> TZS ${Number(d.pledge_paid).toLocaleString()}</p>` : ''}
                        ${d.budget_type ? `<p><strong>Budget Type:</strong> ${d.budget_type}</p>` : ''}
                        ${d.budget_status ? `<p><strong>Budget Status:</strong> ${d.budget_status}</p>` : ''}
                        ${d.purpose ? `<p><strong>Purpose:</strong> ${d.purpose}</p>` : ''}
                        ${d.primary_offering_type ? `<p><strong>Primary Offering:</strong> ${d.primary_offering_type}</p>` : ''}
                        ${d.expense_category ? `<p><strong>Expense Category:</strong> ${d.expense_category}</p>` : ''}
                        ${d.vendor ? `<p><strong>Vendor:</strong> ${d.vendor}</p>` : ''}
                        ${d.receipt_number ? `<p><strong>Receipt:</strong> ${d.receipt_number}</p>` : ''}
                        ${d.notes ? `<hr><p><strong>Notes:</strong><br>${d.notes}</p>` : ''}
                    `;
                })
                .catch(() => {
                    body.innerHTML = '<div class="alert alert-danger mb-0">Failed to load record details.</div>';
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
                    title: 'No records selected',
                    text: 'Select at least one pending record to approve.',
                    confirmButtonColor: document.querySelector('meta[name="brand-color"]')?.content || '#940000',
                });
                return;
            }

            Swal.fire({
                title: 'Approve selected records?',
                text: 'Approve ' + selected.length + ' selected record(s)?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: document.querySelector('meta[name="brand-color"]')?.content || '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, approve',
                cancelButtonText: 'Cancel',
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

                document.getElementById('bulkApprovalNotes').value = 'Bulk approved by {{ auth()->user()->name }}';
                form.submit();
            });
        });
    }
});
</script>
@endpush
