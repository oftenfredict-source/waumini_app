@extends('layouts.church')

@section('title', __('pages.assets.title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-cubes',
    'title' => __('pages.assets.title'),
    'subtitle' => __('pages.assets.subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('pages.assets.breadcrumb')],
    ],
])

<div class="row mb-3">
    <div class="col-md-8">
        <form method="GET" class="form-inline">
            <input type="text" name="search" class="form-control mr-2 mb-2" placeholder="{{ __('pages.assets.search_placeholder') }}"
                value="{{ $filters['search'] ?? '' }}">
            <select name="category" class="form-control mr-2 mb-2">
                <option value="">{{ __('pages.shared.all_categories') }}</option>
                @foreach($categories as $category)
                    <option value="{{ $category->value }}" @selected(($filters['category'] ?? '') === $category->value)>{{ $category->label() }}</option>
                @endforeach
            </select>
            <select name="status" class="form-control mr-2 mb-2">
                <option value="">{{ __('pages.shared.all_statuses') }}</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            @if($canFilterBranches ?? false)
                <select name="branch_id" class="form-control mr-2 mb-2">
                    <option value="">{{ __('pages.shared.all_branches') }}</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) ($filters['branch_id'] ?? '') === (string) $branch->id)>{{ $branch->displayLabel() }}</option>
                    @endforeach
                </select>
            @endif
            <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-search"></i> {{ __('common.filter') }}</button>
        </form>
    </div>
    <div class="col-md-4 text-md-right">
        @can('create', \App\Models\ChurchAsset::class)
            <a href="{{ route('church.assets.create') }}" class="btn btn-primary mb-2">
                <i class="fa fa-plus"></i> {{ __('pages.assets.record_asset') }}
            </a>
        @endcan
    </div>
</div>

<div class="tile">
    <div class="tile-body">
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>{{ __('pages.shared.tag') }}</th>
                        <th>{{ __('common.name') }}</th>
                        <th>{{ __('pages.shared.qty') }}</th>
                        <th>{{ __('common.category') }}</th>
                        <th>{{ __('common.location') }}</th>
                        <th>{{ __('pages.shared.custodian') }}</th>
                        <th>{{ __('pages.shared.value') }}</th>
                        <th>{{ __('common.status') }}</th>
                        <th class="text-right" style="min-width: 140px;">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assets as $asset)
                        <tr>
                            <td><code>{{ $asset->asset_tag }}</code></td>
                            <td>
                                <strong>{{ $asset->name }}</strong>
                                @if($asset->batch_id)
                                    <br><small class="text-muted">{{ __('pages.assets.bulk_item') }}</small>
                                @endif
                                @if($asset->serial_number)
                                    <br><small class="text-muted">{{ __('pages.assets.serial_number', ['sn' => $asset->serial_number]) }}</small>
                                @endif
                            </td>
                            <td>{{ $asset->quantity }}</td>
                            <td>{{ $asset->category->label() }}</td>
                            <td>{{ $asset->location ?? '—' }}</td>
                            <td>{{ $asset->custodian?->full_name ?? '—' }}</td>
                            <td>
                                @if($asset->current_value)
                                    {{ number_format($asset->current_value, 0) }} TZS
                                @elseif($asset->purchase_value)
                                    {{ number_format($asset->purchase_value, 0) }} TZS
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $asset->status->badgeClass() }}">{{ $asset->status->label() }}</span>
                            </td>
                            <td class="text-right text-nowrap">
                                <a href="{{ route('church.assets.show', $asset) }}" class="btn btn-sm btn-info" title="{{ __('common.view') }}">
                                    <i class="fa fa-eye"></i>
                                </a>
                                @can('update', $asset)
                                    <a href="{{ route('church.assets.edit', $asset) }}" class="btn btn-sm btn-warning" title="{{ __('common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('church.assets.destroy', $asset) }}" class="d-inline"
                                        data-swal-confirm="Delete asset {{ $asset->name }}? This cannot be undone."
                                        data-swal-delete
                                        data-swal-confirm-text="{{ __('common.yes_delete') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="{{ __('common.delete') }}">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                {{ __('pages.assets.empty') }}
                                @can('create', \App\Models\ChurchAsset::class)
                                    <a href="{{ route('church.assets.create') }}">{{ __('pages.assets.record_link') }}</a>.
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $assets->links() }}
    </div>
</div>
@endsection
