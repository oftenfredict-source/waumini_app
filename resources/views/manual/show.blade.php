@php
    $layout = match ($scope) {
        'owner' => 'layouts.owner',
        default => 'layouts.church',
    };
    $indexRoute = match ($scope) {
        'owner' => 'owner.manual.index',
        'member' => 'church.member.manual.index',
        default => 'church.manual.index',
    };
@endphp

@extends($layout)

@section('title', $manual['title'])

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-book',
    'title' => $manual['title'],
    'subtitle' => __('manual.modal_title'),
    'breadcrumb' => [
        ['label' => __('manual.index_title'), 'route' => $indexRoute],
        ['label' => $manual['title']],
    ],
])

<div class="tile">
    @if($manual['summary'] !== '')
        <p class="page-manual-summary lead" style="font-size:1rem;">{{ $manual['summary'] }}</p>
    @endif

    @if(! empty($manual['steps']))
        <h5 class="page-manual-section-title">{{ __('manual.how_to') }}</h5>
        <ol class="page-manual-steps">
            @foreach($manual['steps'] as $step)
                <li>{{ $step }}</li>
            @endforeach
        </ol>
    @endif

    @if(! empty($manual['tips']))
        <h5 class="page-manual-section-title">{{ __('manual.tips') }}</h5>
        <ul class="page-manual-tips">
            @foreach($manual['tips'] as $tip)
                <li>{{ $tip }}</li>
            @endforeach
        </ul>
    @endif

    <a href="{{ route($indexRoute) }}" class="btn btn-outline-secondary mt-2">
        <i class="fa fa-arrow-left"></i> {{ __('manual.view_all') }}
    </a>
</div>
@endsection
