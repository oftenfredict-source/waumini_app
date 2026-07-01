@extends('layouts.church')

@section('title', __('pages.members_children.title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-child',
    'title' => __('pages.members_children.title'),
    'subtitle' => __('pages.members_children.subtitle', ['age' => $independenceAge]),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.members'), 'route' => 'church.members.index'],
        ['label' => __('pages.members_children.title')],
    ],
])

<div class="row mb-3">
    <div class="col-md-3">
        <div class="widget-small primary coloured-icon">
            <i class="icon fa fa-child fa-3x"></i>
            <div class="info">
                <h4>{{ __('pages.members_children.total_children') }}</h4>
                <p><b>{{ $stats['total'] }}</b></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small warning coloured-icon">
            <i class="icon fa fa-clock-o fa-3x"></i>
            <div class="info">
                <h4>{{ __('pages.members_children.ready_age', ['age' => $independenceAge]) }}</h4>
                <p><b>{{ $stats['eligible'] }}</b></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small success coloured-icon">
            <i class="icon fa fa-user fa-3x"></i>
            <div class="info">
                <h4>{{ __('pages.members_children.converted') }}</h4>
                <p><b>{{ $stats['converted'] }}</b></p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-8">
        <form method="GET" class="form-inline">
            <input type="text" name="search" class="form-control mr-2 mb-2" placeholder="{{ __('pages.members_children.search_placeholder') }}"
                value="{{ $filters['search'] ?? '' }}">
            <select name="status" class="form-control mr-2 mb-2">
                <option value="">{{ __('pages.shared.all_children') }}</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>{{ __('pages.members_children.under_age', ['age' => $independenceAge]) }}</option>
                <option value="eligible" @selected(($filters['status'] ?? '') === 'eligible')>{{ __('pages.members_children.ready_to_convert', ['age' => $independenceAge]) }}</option>
                <option value="converted" @selected(($filters['status'] ?? '') === 'converted')>{{ __('pages.members_children.converted') }}</option>
            </select>
            <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-search"></i> {{ __('common.search') }}</button>
        </form>
    </div>
    <div class="col-md-4 text-md-right">
        @can('create', \App\Models\MemberDependant::class)
            <a href="{{ route('church.members.children.create') }}" class="btn btn-primary mb-2 mr-1">
                <i class="fa fa-plus"></i> {{ __('pages.members_children.add_child') }}
            </a>
        @endcan
        @can('viewAny', \App\Models\MemberDependant::class)
            @if($stats['eligible'] > 0)
                <form method="POST" action="{{ route('church.members.children.process-aged-out') }}" class="d-inline"
                    data-swal-confirm="{{ __('pages.members_children.convert_all_confirm', ['count' => $stats['eligible']]) }}">
                    @csrf
                    <button type="submit" class="btn btn-warning mb-2">
                        <i class="fa fa-magic"></i> {{ __('pages.members_children.convert_all_eligible') }}
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
                        <th>{{ __('pages.members_children.child_name') }}</th>
                        <th>{{ __('pages.members_children.gender_col') }}</th>
                        <th>{{ __('pages.members_children.date_of_birth') }}</th>
                        <th>{{ __('pages.members_children.age_col') }}</th>
                        <th>{{ __('pages.members_children.parent_guardian') }}</th>
                        <th>{{ __('common.status') }}</th>
                        <th width="220">{{ __('common.actions') }}</th>
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
                                    <br><span class="badge badge-secondary">{{ __('pages.members_children.non_member_guardian') }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if($child->isConverted())
                                    <span class="badge badge-success">{{ __('pages.members_children.independent_member') }}</span>
                                @elseif($child->isEligibleForIndependence())
                                    <span class="badge badge-warning">{{ __('pages.members_children.ready_age', ['age' => $independenceAge]) }}</span>
                                @else
                                    <span class="badge badge-info">{{ __('pages.members_children.active_child') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($child->linkedMember)
                                    <a href="{{ route('church.members.show', $child->linkedMember) }}" class="btn btn-sm btn-success">
                                        <i class="fa fa-user"></i> {{ __('pages.members_children.view_member') }}
                                    </a>
                                @elseif($child->isEligibleForIndependence())
                                    @can('convert', $child)
                                        <form method="POST" action="{{ route('church.members.children.convert', $child) }}" class="form-inline">
                                            @csrf
                                            <input type="text" name="envelope_number" class="form-control form-control-sm mr-1"
                                                placeholder="{{ __('pages.members_children.env_placeholder') }}" maxlength="3" pattern="\d{3}" required style="width:70px;">
                                            <button type="submit" class="btn btn-sm btn-primary" title="{{ __('pages.members_children.convert_title') }}">
                                                <i class="fa fa-user-plus"></i> {{ __('pages.members_children.convert') }}
                                            </button>
                                        </form>
                                    @endcan
                                @else
                                    <span class="text-muted small">{{ __('pages.members_children.under_years', ['age' => $independenceAge]) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                {{ __('pages.members_children.no_children') }}
                                @can('create', \App\Models\MemberDependant::class)
                                    <a href="{{ route('church.members.children.create') }}">{{ __('pages.members_children.add_child_link') }}</a>
                                @else
                                    {{ __('pages.members_children.add_via_family') }}
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
