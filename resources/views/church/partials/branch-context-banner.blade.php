@if(($isInBranchContext ?? false) && ($activeBranchContext ?? null))
    <div class="branch-context-bar" role="status">
        <div class="branch-context-bar__info">
            <span class="branch-context-bar__dot" aria-hidden="true"></span>
            <i class="fa fa-code-fork" aria-hidden="true"></i>
            <span class="branch-context-bar__label">{{ __('pages.branches.viewing') }}</span>
            <strong class="branch-context-bar__name">{{ $activeBranchContext->name }}</strong>
            @if($activeBranchContext->code)
                <span class="branch-context-bar__code">{{ $activeBranchContext->code }}</span>
            @endif
        </div>
        <form action="{{ route('church.branches.exit') }}" method="POST" class="mb-0">
            @csrf
            <button type="submit" class="branch-context-bar__exit">
                <i class="fa fa-times" aria-hidden="true"></i>
                <span>{{ __('pages.branches.exit_all') }}</span>
            </button>
        </form>
    </div>
@endif
