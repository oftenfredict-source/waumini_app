@extends('layouts.church')

@section('title', $asset->name)

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-cube',
    'title' => $asset->name,
    'subtitle' => __('pages.shared.asset_tag_label', ['tag' => $asset->asset_tag]),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('pages.assets.breadcrumb'), 'route' => 'church.assets.index'],
        ['label' => $asset->name],
    ],
])

<div class="row">
    <div class="col-lg-8">
        <div class="tile mb-3">
            <h3 class="tile-title">{{ __('pages.shared.asset_details') }}</h3>
            <table class="table table-borderless mb-0">
                <tr><th width="180">{{ __('pages.shared.asset_tag') }}</th><td><code>{{ $asset->asset_tag }}</code></td></tr>
                <tr><th>{{ __('common.name') }}</th><td>{{ $asset->name }}</td></tr>
                <tr><th>{{ __('pages.shared.quantity') }}</th><td>{{ $asset->quantity }}</td></tr>
                @if($asset->batch_id)
                    <tr><th>{{ __('pages.assets.registration') }}</th><td>{{ __('pages.assets.bulk_registration') }}</td></tr>
                @endif
                <tr><th>{{ __('common.category') }}</th><td>{{ $asset->category->label() }}</td></tr>
                <tr><th>{{ __('common.status') }}</th>
                    <td><span class="badge badge-{{ $asset->status->badgeClass() }}">{{ $asset->status->label() }}</span></td>
                </tr>
                <tr><th>{{ __('pages.shared.condition') }}</th><td>{{ $asset->condition->label() }}</td></tr>
                <tr><th>{{ __('pages.shared.serial_number_label') }}</th><td>{{ $asset->serial_number ?? '—' }}</td></tr>
                <tr><th>{{ __('common.location') }}</th><td>{{ $asset->location ?? '—' }}</td></tr>
                @if($asset->branch)
                    <tr><th>{{ __('common.branch') }}</th><td>{{ $asset->branch->displayLabel() }}</td></tr>
                @endif
                <tr><th>{{ __('pages.shared.custodian') }}</th>
                    <td>
                        @if($asset->custodian)
                            <a href="{{ route('church.members.show', $asset->custodian) }}">{{ $asset->custodian->full_name }}</a>
                        @else
                            —
                        @endif
                    </td>
                </tr>
                <tr><th>{{ __('pages.shared.purchase_date') }}</th><td>{{ $asset->purchase_date?->format('M d, Y') ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.purchase_value') }}</th><td>{{ $asset->purchase_value ? number_format($asset->purchase_value, 0).' TZS' : '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.current_value') }}</th><td>{{ $asset->current_value ? number_format($asset->current_value, 0).' TZS' : '—' }}</td></tr>
                @if($asset->disposed_at)
                    <tr><th>{{ __('pages.shared.disposed_date') }}</th><td>{{ $asset->disposed_at->format('M d, Y') }}</td></tr>
                @endif
                <tr><th>{{ __('common.description') }}</th><td>{{ $asset->description ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.notes') }}</th><td>{{ $asset->notes ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.recorded_by') }}</th><td>{{ $asset->recorder?->name ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.recorded_on') }}</th><td>{{ $asset->created_at->format('M d, Y H:i') }}</td></tr>
            </table>
        </div>
    </div>

    <div class="col-lg-4">
        @if($asset->photoUrl())
            <div class="tile mb-3">
                <h3 class="tile-title">{{ __('pages.shared.photo') }}</h3>
                <img src="{{ $asset->photoUrl() }}" alt="{{ $asset->name }}" class="img-fluid rounded">
            </div>
        @endif

        <div class="tile">
            <h3 class="tile-title">{{ __('common.actions') }}</h3>
            <a href="{{ route('church.assets.index') }}" class="btn btn-secondary btn-block">
                <i class="fa fa-arrow-left"></i> {{ __('pages.assets.back_to') }}
            </a>
            @can('update', $asset)
                <a href="{{ route('church.assets.edit', $asset) }}" class="btn btn-primary btn-block mt-2">
                    <i class="fa fa-pencil"></i> {{ __('pages.assets.edit_asset') }}
                </a>
            @endcan
        </div>
    </div>
</div>
@endsection
