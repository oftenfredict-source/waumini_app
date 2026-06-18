@extends('layouts.owner')

@section('title', 'Payments')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-money"></i> Payments</h1>
        <p>Track church payments and billing</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('owner.dashboard') }}">Overview</a></li>
        <li class="breadcrumb-item">Payments</li>
    </ul>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="widget-small primary coloured-icon">
            <i class="icon fa fa-list fa-3x"></i>
            <div class="info"><h4>Total</h4><p><b>{{ $stats['total'] }}</b></p></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small success coloured-icon">
            <i class="icon fa fa-check fa-3x"></i>
            <div class="info"><h4>Completed</h4><p><b>{{ $stats['completed'] }}</b></p></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small warning coloured-icon">
            <i class="icon fa fa-clock-o fa-3x"></i>
            <div class="info"><h4>Pending</h4><p><b>{{ $stats['pending'] }}</b></p></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small info coloured-icon">
            <i class="icon fa fa-dollar fa-3x"></i>
            <div class="info"><h4>Collected</h4><p><b>${{ number_format($stats['revenue'], 2) }}</b></p></div>
        </div>
    </div>
</div>

<div class="tile">
    <form method="GET" class="form-inline mb-3">
        <select name="status" class="form-control mr-2">
            <option value="">All statuses</option>
            <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Pending</option>
            <option value="completed" @selected(($filters['status'] ?? '') === 'completed')>Completed</option>
            <option value="failed" @selected(($filters['status'] ?? '') === 'failed')>Failed</option>
        </select>
        <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Filter</button>
    </form>

    <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th>Church</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th>Paid At</th>
                    <th>Reference</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->church?->name }}</td>
                        <td>{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</td>
                        <td>{{ ucfirst($payment->method) }}</td>
                        <td>
                            <span class="badge badge-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </td>
                        <td>{{ $payment->paid_at?->format('M d, Y H:i') ?? '—' }}</td>
                        <td>{{ $payment->provider_reference ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="fa fa-inbox fa-2x d-block mb-2"></i>
                            No payments recorded yet. Payments will appear here when churches are billed.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $payments->links() }}
</div>
@endsection
