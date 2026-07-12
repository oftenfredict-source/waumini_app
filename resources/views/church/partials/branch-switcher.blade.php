@if($canSwitchBranches ?? false)
    <li class="dropdown branch-switcher{{ ($isInBranchContext ?? false) ? ' branch-switcher--active' : '' }}">
        <a class="app-nav__item branch-switcher__toggle" href="#" data-toggle="dropdown" aria-label="{{ __('pages.branches.switch_branch') }}">
            <i class="fa fa-code-fork" aria-hidden="true"></i>
            <span class="branch-switcher__text d-none d-md-inline">
                {{ $activeBranchContext?->code ?? __('pages.shared.all_branches') }}
            </span>
            <i class="fa fa-angle-down branch-switcher__caret d-none d-md-inline" aria-hidden="true"></i>
        </a>
        <ul class="dropdown-menu settings-menu dropdown-menu-right branch-switcher__menu">
            <li><span class="dropdown-item-text text-muted small">{{ __('pages.branches.switch_branch') }}</span></li>
            @foreach($branchSwitcherBranches as $switchBranch)
                <li>
                    <form action="{{ route('church.branches.enter', $switchBranch) }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item{{ ($activeBranchContext?->id === $switchBranch->id) ? ' active' : '' }}">
                            <span>{{ $switchBranch->name }}</span>
                            @if($switchBranch->is_headquarters)
                                <small class="text-muted">HQ</small>
                            @endif
                        </button>
                    </form>
                </li>
            @endforeach
            @if($isInBranchContext ?? false)
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="{{ route('church.branches.exit') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="fa fa-globe"></i> {{ __('pages.branches.exit_all') }}
                        </button>
                    </form>
                </li>
            @endif
        </ul>
    </li>
@endif
