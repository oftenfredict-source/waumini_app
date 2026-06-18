@extends('layouts.church')

@section('title', 'Children')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-child"></i> Children</h1>
        <p>Dependants registered as children — convert to independent members at age {{ $independenceAge }}+</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.members.index') }}">Members</a></li>
        <li class="breadcrumb-item">Children</li>
    </ul>
</div>

<div class="row mb-3">
    <div class="col-md-3">
        <div class="widget-small primary coloured-icon">
            <i class="icon fa fa-child fa-3x"></i>
            <div class="info">
                <h4>Total Children</h4>
                <p><b>{{ $stats['total'] }}</b></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small warning coloured-icon">
            <i class="icon fa fa-clock-o fa-3x"></i>
            <div class="info">
                <h4>Ready ({{ $independenceAge }}+)</h4>
                <p><b>{{ $stats['eligible'] }}</b></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small success coloured-icon">
            <i class="icon fa fa-user fa-3x"></i>
            <div class="info">
                <h4>Converted</h4>
                <p><b>{{ $stats['converted'] }}</b></p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-8">
        <form method="GET" class="form-inline">
            <input type="text" name="search" class="form-control mr-2 mb-2" placeholder="Search child or parent..."
                value="{{ $filters['search'] ?? '' }}">
            <select name="status" class="form-control mr-2 mb-2">
                <option value="">All children</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Under {{ $independenceAge }}</option>
                <option value="eligible" @selected(($filters['status'] ?? '') === 'eligible')>Ready to convert ({{ $independenceAge }}+)</option>
                <option value="converted" @selected(($filters['status'] ?? '') === 'converted')>Converted</option>
            </select>
            <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-search"></i> Search</button>
        </form>
    </div>
    <div class="col-md-4 text-md-right">
        @can('create', \App\Models\MemberDependant::class)
            <a href="{{ route('church.members.children.create') }}" class="btn btn-primary mb-2 mr-1">
                <i class="fa fa-plus"></i> Add Child
            </a>
        @endcan
        @can('viewAny', \App\Models\MemberDependant::class)
            @if($stats['eligible'] > 0)
                <form method="POST" action="{{ route('church.members.children.process-aged-out') }}" class="d-inline"
                    data-swal-confirm="Convert all {{ $stats['eligible'] }} eligible child(ren) to independent members? The next available envelope numbers will be assigned automatically.">
                    @csrf
                    <button type="submit" class="btn btn-warning mb-2">
                        <i class="fa fa-magic"></i> Convert All Eligible
                    </button>
                </form>
            @endif
        @endcan
    </div>
</div>

<div class="tile">
    <div class="tile-body">
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>Child Name</th>
                        <th>Gender</th>
                        <th>Date of Birth</th>
                        <th>Age</th>
                        <th>Parent / Guardian</th>
                        <th>Status</th>
                        <th width="220">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($children as $child)
                        <tr>
                            <td>{{ $child->full_name }}</td>
                            <td>{{ ucfirst($child->gender) }}</td>
                            <td>{{ $child->date_of_birth?->format('M d, Y') ?? '—' }}</td>
                            <td>{{ $child->age() ?? '—' }}</td>
                            <td>
                                @if($child->member)
                                    <a href="{{ route('church.members.show', $child->member) }}">{{ $child->member->full_name }}</a>
                                @elseif($child->guardian_full_name)
                                    {{ $child->guardian_full_name }}
                                    @if($child->guardian_relationship)
                                        <small class="text-muted">({{ $child->guardian_relationship }})</small>
                                    @endif
                                    <br><span class="badge badge-secondary">Non-member guardian</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if($child->isConverted())
                                    <span class="badge badge-success">Independent member</span>
                                @elseif($child->isEligibleForIndependence())
                                    <span class="badge badge-warning">Ready ({{ $independenceAge }}+)</span>
                                @else
                                    <span class="badge badge-info">Active child</span>
                                @endif
                            </td>
                            <td>
                                @if($child->linkedMember)
                                    <a href="{{ route('church.members.show', $child->linkedMember) }}" class="btn btn-sm btn-success">
                                        <i class="fa fa-user"></i> View Member
                                    </a>
                                @elseif($child->isEligibleForIndependence())
                                    @can('convert', $child)
                                        <form method="POST" action="{{ route('church.members.children.convert', $child) }}" class="form-inline">
                                            @csrf
                                            <input type="text" name="envelope_number" class="form-control form-control-sm mr-1"
                                                placeholder="Env #" maxlength="3" pattern="\d{3}" required style="width:70px;">
                                            <button type="submit" class="btn btn-sm btn-primary" title="Convert to independent member">
                                                <i class="fa fa-user-plus"></i> Convert
                                            </button>
                                        </form>
                                    @endcan
                                @else
                                    <span class="text-muted small">Under {{ $independenceAge }} years</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No children found.
                                @can('create', \App\Models\MemberDependant::class)
                                    <a href="{{ route('church.members.children.create') }}">Add a child</a>
                                @else
                                    Add children when registering a member under Family Information.
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $children->links() }}
    </div>
</div>
@endsection
