@extends('layouts.owner')

@section('title', 'Overview')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-dashboard"></i> Owner Dashboard</h1>
        <p>Platform overview and analytics</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
        <li class="breadcrumb-item"><a href="#">Overview</a></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-6 col-lg-3">
        <div class="widget-small primary coloured-icon">
            <i class="icon fa fa-building fa-3x"></i>
            <div class="info">
                <h4>Total Churches</h4>
                <p><b>{{ number_format($overview['total_churches']) }}</b></p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="widget-small info coloured-icon">
            <i class="icon fa fa-check-circle fa-3x"></i>
            <div class="info">
                <h4>Active Churches</h4>
                <p><b>{{ number_format($overview['active_churches']) }}</b></p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="widget-small warning coloured-icon">
            <i class="icon fa fa-pause-circle fa-3x"></i>
            <div class="info">
                <h4>Suspended</h4>
                <p><b>{{ number_format($overview['suspended_churches']) }}</b></p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="widget-small danger coloured-icon">
            <i class="icon fa fa-clock-o fa-3x"></i>
            <div class="info">
                <h4>Expired</h4>
                <p><b>{{ number_format($overview['expired_churches']) }}</b></p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 col-lg-3">
        <div class="widget-small primary coloured-icon">
            <i class="icon fa fa-user-plus fa-3x"></i>
            <div class="info">
                <h4>New Signups (30d)</h4>
                <p><b>{{ number_format($overview['new_signups_30d']) }}</b></p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="widget-small info coloured-icon">
            <i class="icon fa fa-calendar fa-3x"></i>
            <div class="info">
                <h4>New Signups (7d)</h4>
                <p><b>{{ number_format($overview['new_signups_7d']) }}</b></p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="widget-small success coloured-icon">
            <i class="icon fa fa-money fa-3x"></i>
            <div class="info">
                <h4>Monthly Revenue (MRR)</h4>
                <p><b>${{ number_format($overview['mrr'], 2) }}</b></p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="widget-small warning coloured-icon">
            <i class="icon fa fa-line-chart fa-3x"></i>
            <div class="info">
                <h4>Yearly Revenue (ARR)</h4>
                <p><b>${{ number_format($overview['arr'], 2) }}</b></p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="tile">
            <h3 class="tile-title">Church Signups (12 months)</h3>
            <div class="embed-responsive embed-responsive-16by9">
                <canvas class="embed-responsive-item" id="signupsChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="tile">
            <h3 class="tile-title">Churches by Package</h3>
            <div class="embed-responsive embed-responsive-16by9">
                <canvas class="embed-responsive-item" id="packageChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="tile">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="tile-title mb-0">Recent Churches</h3>
                <a href="{{ route('owner.churches.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> Add Church
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>Church</th>
                            <th>Email</th>
                            <th>Package</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentChurches as $church)
                            <tr>
                                <td>{{ $church->name }}</td>
                                <td>{{ $church->email }}</td>
                                <td>{{ $church->activeSubscription?->package?->name ?? '—' }}</td>
                                <td>@include('owner.components.status-badge', ['status' => $church->status])</td>
                                <td>{{ $church->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('owner.churches.show', $church) }}" class="btn btn-sm btn-info">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No churches registered yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vali-master/docs/js/plugins/chart.js') }}"></script>
<script>
    var signupsData = {
        labels: @json($signupsChart['labels']),
        datasets: [{
            label: 'New Churches',
            fillColor: 'rgba(0, 150, 136, 0.2)',
            strokeColor: 'rgba(0, 150, 136, 1)',
            pointColor: 'rgba(0, 150, 136, 1)',
            data: @json($signupsChart['data'])
        }]
    };
    new Chart(document.getElementById('signupsChart').getContext('2d')).Line(signupsData);

    var packageLabels = @json($churchesByPackage->keys()->values());
    var packageData = @json($churchesByPackage->values());
    var colors = ['#46BFBD', '#F7464A', '#FDB45C', '#949FB1', '#4D5360'];
    var packageChartData = packageLabels.map(function(label, i) {
        return { value: packageData[i], color: colors[i % colors.length], label: label };
    });
    if (packageChartData.length) {
        new Chart(document.getElementById('packageChart').getContext('2d')).Pie(packageChartData);
    }
</script>
@endpush
