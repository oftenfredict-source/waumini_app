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
    $showRoute = match ($scope) {
        'owner' => 'owner.manual.show',
        'member' => 'church.member.manual.show',
        default => 'church.manual.show',
    };
@endphp

@extends($layout)

@section('title', __('manual.index_title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-book',
    'title' => __('manual.index_title'),
    'subtitle' => __('manual.index_subtitle'),
])

<div class="tile mb-3">
    <input type="search" id="manualSearch" class="form-control" placeholder="{{ __('manual.search_placeholder') }}" autocomplete="off">
</div>

<div class="row" id="manualTopics">
    @foreach($topics as $topic)
        <div class="col-md-6 col-lg-4 mb-3 manual-topic-card" data-search="{{ strtolower($topic['title'].' '.$topic['summary']) }}">
            <div class="tile page-manual-card p-3">
                <h3 class="tile-title mb-2">{{ $topic['title'] }}</h3>
                <p class="text-muted mb-3">{{ $topic['summary'] }}</p>
                <a href="{{ route($showRoute, $topic['key']) }}" class="btn btn-sm btn-outline-primary">
                    {{ __('manual.open_topic') }}
                </a>
            </div>
        </div>
    @endforeach
</div>

<p id="manualEmpty" class="text-muted text-center d-none py-4">{{ __('manual.no_results') }}</p>
@endsection

@push('scripts')
<script>
(function () {
    var input = document.getElementById('manualSearch');
    var cards = document.querySelectorAll('.manual-topic-card');
    var empty = document.getElementById('manualEmpty');
    if (!input) return;

    input.addEventListener('input', function () {
        var q = (input.value || '').toLowerCase().trim();
        var visible = 0;
        cards.forEach(function (card) {
            var match = !q || (card.getAttribute('data-search') || '').indexOf(q) !== -1;
            card.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        if (empty) empty.classList.toggle('d-none', visible > 0);
    });
})();
</script>
@endpush
