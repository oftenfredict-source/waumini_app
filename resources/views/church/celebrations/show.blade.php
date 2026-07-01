@extends('layouts.church')

@section('title', $celebration->title)

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-birthday-cake',
    'title' => $celebration->title,
    'subtitle' => $celebration->celebration_type->label() . ' — ' . $celebration->celebration_date->format('M d, Y'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.celebrations'), 'route' => 'church.celebrations.index'],
        ['label' => __('pages.shared.breadcrumb_details')],
    ],
])

<div class="row">
    <div class="col-md-8">
        <div class="tile">
            <table class="table table-borderless table-sm">
                <tr><th width="180">{{ __('common.type') }}</th><td><span class="badge badge-{{ $celebration->celebration_type->badgeClass() }}">{{ $celebration->celebration_type->label() }}</span></td></tr>
                <tr><th>{{ __('pages.shared.source') }}</th><td>{{ $celebration->source->label() }}</td></tr>
                <tr><th>{{ __('common.status') }}</th><td><span class="badge badge-{{ $celebration->status->badgeClass() }}">{{ $celebration->status->label() }}</span></td></tr>
                <tr><th>{{ __('pages.shared.celebration_date') }}</th><td>{{ $celebration->celebration_date->format('l, M d, Y') }}</td></tr>
                @if($celebration->original_date)
                    <tr><th>{{ __('pages.shared.original_date') }}</th><td>{{ $celebration->original_date->format('M d, Y') }}</td></tr>
                @endif
                @if($celebration->yearsCount())
                    <tr><th>{{ __('pages.shared.milestone') }}</th><td>{{ $celebration->yearsCount() }} {{ $celebration->celebration_type === \App\Enums\CelebrationType::WeddingAnniversary ? __('pages.celebrations.years_married') : __('pages.celebrations.years') }}</td></tr>
                @endif
                @if($celebration->wedding_type)
                    <tr><th>{{ __('pages.shared.wedding_type') }}</th><td>{{ $celebration->wedding_type->label() }}</td></tr>
                @endif
                @if($celebration->member)
                    <tr><th>{{ __('common.member') }}</th><td><a href="{{ route('church.members.show', $celebration->member) }}">{{ $celebration->member->full_name }}</a></td></tr>
                @endif
                <tr><th>{{ __('pages.shared.notes') }}</th><td>{{ $celebration->notes ?? '—' }}</td></tr>
                <tr><th>{{ __('common.created') }}</th><td>
                    @if($celebration->creator)
                        {{ __('pages.shared.created_at_by', ['date' => $celebration->created_at->format('M d, Y H:i'), 'name' => $celebration->creator->name]) }}
                    @else
                        {{ $celebration->created_at->format('M d, Y H:i') }}
                    @endif
                </td></tr>
            </table>
        </div>
    </div>
    <div class="col-md-4">
        <div class="tile">
            <h3 class="tile-title">{{ __('common.actions') }}</h3>
            <a href="{{ route('church.celebrations.index') }}" class="btn btn-secondary btn-block mb-2">
                <i class="fa fa-arrow-left"></i> {{ __('pages.celebrations.back_to') }}
            </a>
            @can('update', $celebration)
                <a href="{{ route('church.celebrations.edit', $celebration) }}" class="btn btn-primary btn-block mb-2">
                    <i class="fa fa-pencil"></i> {{ __('common.edit') }}
                </a>
            @endcan
            @can('delete', $celebration)
                <form method="POST" action="{{ route('church.celebrations.destroy', $celebration) }}"
                    data-swal-confirm="{{ __('pages.celebrations.remove_confirm') }}" data-swal-delete>
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block">
                        <i class="fa fa-trash"></i>
                        @if($celebration->source === \App\Enums\CelebrationSource::Auto)
                            {{ __('pages.celebrations.cancel_auto_entry') }}
                        @else
                            {{ __('pages.celebrations.delete_celebration') }}
                        @endif
                    </button>
                </form>
            @endcan
        </div>
    </div>
</div>
@endsection
