@extends('layouts.owner')

@section('title', __('owner.rev.title'))

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-bar-chart"></i> {{ __('owner.rev.heading') }}</h1>
        <p>{{ __('owner.rev.subtitle') }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('owner.dashboard') }}">{{ __('owner.overview') }}</a></li>
        <li class="breadcrumb-item">{{ __('owner.revenue') }}</li>
    </ul>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="widget-small success coloured-icon">
            <i class="icon fa fa-money fa-3x"></i>
            <div class="info"><h4>{{ __('owner.rev.mrr') }}</h4><p><b>${{ number_format($overview['mrr'], 2) }}</b></p></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="widget-small primary coloured-icon">
            <i class="icon fa fa-line-chart fa-3x"></i>
            <div class="info"><h4>{{ __('owner.rev.arr') }}</h4><p><b>${{ number_format($overview['arr'], 2) }}</b></p></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="widget-small info coloured-icon">
            <i class="icon fa fa-building fa-3x"></i>
            <div class="info"><h4>{{ __('owner.rev.paying_churches') }}</h4><p><b>{{ $overview['active_churches'] }}</b></p></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="tile">
            <h3 class="tile-title">{{ __('owner.rev.collected_12m') }}</h3>
            <div class="embed-responsive embed-responsive-16by9">
                <canvas class="embed-responsive-item" id="revenueChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="tile">
            <h3 class="tile-title">{{ __('owner.rev.by_package') }}</h3>
            <div class="embed-responsive embed-responsive-16by9">
                <canvas class="embed-responsive-item" id="packageChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="tile">
            <h3 class="tile-title">{{ __('owner.rev.signups') }}</h3>
            <div class="embed-responsive embed-responsive-16by9">
                <canvas class="embed-responsive-item" id="signupsChart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vali-master/docs/js/plugins/chart.js') }}"></script>
<script>
    new Chart(document.getElementById('revenueChart').getContext('2d')).Line({
        labels: @json($monthlyRevenue['labels']),
        datasets: [{
            label: @json(__('owner.rev.chart_revenue')),
            fillColor: 'rgba(40, 167, 69, 0.2)',
            strokeColor: 'rgba(40, 167, 69, 1)',
            pointColor: 'rgba(40, 167, 69, 1)',
            data: @json($monthlyRevenue['data'])
        }]
    });

    var pkgLabels = @json($churchesByPackage->keys()->values());
    var pkgData = @json($churchesByPackage->values());
    var colors = ['#46BFBD', '#F7464A', '#FDB45C', '#949FB1'];
    if (pkgLabels.length) {
        new Chart(document.getElementById('packageChart').getContext('2d')).Pie(
            pkgLabels.map(function(l, i) { return { label: l, value: pkgData[i], color: colors[i % colors.length] }; })
        );
    }

    new Chart(document.getElementById('signupsChart').getContext('2d')).Bar({
        labels: @json($signupsChart['labels']),
        datasets: [{
            label: @json(__('owner.rev.chart_signups')),
            fillColor: 'rgba(0, 123, 255, 0.5)',
            strokeColor: 'rgba(0, 123, 255, 1)',
            data: @json($signupsChart['data'])
        }]
    });
</script>
@endpush
