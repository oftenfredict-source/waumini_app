@php
    $pageManual = $pageManual ?? \App\Support\PageManual::forCurrentRoute();
    $manualIndexRoute = request()->routeIs('owner.*')
        ? 'owner.manual.index'
        : (request()->routeIs('church.member.*') ? 'church.member.manual.index' : 'church.manual.index');
    $canBrowseManual = \Illuminate\Support\Facades\Route::has($manualIndexRoute);
@endphp

<button type="button"
        class="page-manual-fab"
        data-toggle="modal"
        data-target="#pageManualModal"
        title="{{ __('manual.button_title') }}"
        aria-label="{{ __('manual.button_title') }}">
    <i class="fa fa-question-circle" aria-hidden="true"></i>
    <span class="page-manual-fab__label">{{ __('manual.button') }}</span>
</button>

<div class="modal fade" id="pageManualModal" tabindex="-1" role="dialog" aria-labelledby="pageManualModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content page-manual-modal">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0" id="pageManualModalLabel">
                        <i class="fa fa-book"></i> {{ __('manual.modal_title') }}
                    </h5>
                    <small class="text-muted">{{ $pageManual['title'] }}</small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('manual.close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @if($pageManual['summary'] !== '')
                    <p class="page-manual-summary">{{ $pageManual['summary'] }}</p>
                @endif

                @if(! empty($pageManual['steps']))
                    <h6 class="page-manual-section-title">{{ __('manual.how_to') }}</h6>
                    <ol class="page-manual-steps">
                        @foreach($pageManual['steps'] as $step)
                            <li>{{ $step }}</li>
                        @endforeach
                    </ol>
                @endif

                @if(! empty($pageManual['tips']))
                    <h6 class="page-manual-section-title">{{ __('manual.tips') }}</h6>
                    <ul class="page-manual-tips">
                        @foreach($pageManual['tips'] as $tip)
                            <li>{{ $tip }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <div class="modal-footer">
                @if($canBrowseManual)
                    <a href="{{ route($manualIndexRoute) }}" class="btn btn-outline-secondary mr-auto">
                        <i class="fa fa-list"></i> {{ __('manual.view_all') }}
                    </a>
                @endif
                <button type="button" class="btn btn-primary" data-dismiss="modal">{{ __('manual.close') }}</button>
            </div>
        </div>
    </div>
</div>
