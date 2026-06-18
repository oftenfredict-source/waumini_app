@extends('layouts.vali')

@section('title', 'Dashboard')
@section('menu_dashboard_active', 'active')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-dashboard"></i> Dashboard</h1>
        <p>Welcome to {{ config('app.name') }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
        <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-6 col-lg-3">
        <div class="widget-small primary coloured-icon">
            <i class="icon fa fa-users fa-3x"></i>
            <div class="info">
                <h4>Users</h4>
                <p><b>5</b></p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="widget-small info coloured-icon">
            <i class="icon fa fa-thumbs-o-up fa-3x"></i>
            <div class="info">
                <h4>Likes</h4>
                <p><b>25</b></p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="widget-small warning coloured-icon">
            <i class="icon fa fa-files-o fa-3x"></i>
            <div class="info">
                <h4>Uploads</h4>
                <p><b>10</b></p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="widget-small danger coloured-icon">
            <i class="icon fa fa-star fa-3x"></i>
            <div class="info">
                <h4>Stars</h4>
                <p><b>500</b></p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="tile">
            <h3 class="tile-title">Monthly Sales</h3>
            <div class="embed-responsive embed-responsive-16by9">
                <canvas class="embed-responsive-item" id="lineChartDemo"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="tile">
            <h3 class="tile-title">Support Requests</h3>
            <div class="embed-responsive embed-responsive-16by9">
                <canvas class="embed-responsive-item" id="pieChartDemo"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="tile">
            <h3 class="tile-title">Features</h3>
            <ul>
                <li>Built with Bootstrap 4, SASS and PUG.js</li>
                <li>Fully responsive and modular code</li>
                <li>Chart.js integration to display responsive charts</li>
                <li>Widgets to effectively display statistics</li>
                <li>Data tables with sort, search and paginate functionality</li>
            </ul>
            <p>Vali is a free and responsive admin theme integrated with Laravel for {{ config('app.name') }}.</p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="tile">
            <h3 class="tile-title">Quick Links</h3>
            <p>Use the sidebar to explore charts, forms, tables, and other template pages.</p>
            <p class="mt-4 mb-4">
                <a class="btn btn-primary mr-2 mb-2" href="{{ asset('vali-master/docs/charts.html') }}"><i class="fa fa-pie-chart"></i> Charts</a>
                <a class="btn btn-info mr-2 mb-2" href="{{ asset('vali-master/docs/table-data-table.html') }}"><i class="fa fa-table"></i> Data Tables</a>
                <a class="btn btn-success mr-2 mb-2" href="{{ asset('vali-master/docs/page-login.html') }}"><i class="fa fa-sign-in"></i> Login</a>
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript" src="{{ asset('vali-master/docs/js/plugins/chart.js') }}"></script>
<script type="text/javascript">
    var data = {
        labels: ["January", "February", "March", "April", "May"],
        datasets: [
            {
                label: "My First dataset",
                fillColor: "rgba(220,220,220,0.2)",
                strokeColor: "rgba(220,220,220,1)",
                pointColor: "rgba(220,220,220,1)",
                pointStrokeColor: "#fff",
                pointHighlightFill: "#fff",
                pointHighlightStroke: "rgba(220,220,220,1)",
                data: [65, 59, 80, 81, 56]
            },
            {
                label: "My Second dataset",
                fillColor: "rgba(151,187,205,0.2)",
                strokeColor: "rgba(151,187,205,1)",
                pointColor: "rgba(151,187,205,1)",
                pointStrokeColor: "#fff",
                pointHighlightFill: "#fff",
                pointHighlightStroke: "rgba(151,187,205,1)",
                data: [28, 48, 40, 19, 86]
            }
        ]
    };
    var pdata = [
        { value: 300, color: "#46BFBD", highlight: "#5AD3D1", label: "Complete" },
        { value: 50, color: "#F7464A", highlight: "#FF5A5E", label: "In-Progress" }
    ];

    var ctxl = $("#lineChartDemo").get(0).getContext("2d");
    new Chart(ctxl).Line(data);

    var ctxp = $("#pieChartDemo").get(0).getContext("2d");
    new Chart(ctxp).Pie(pdata);
</script>
@endpush
