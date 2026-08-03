@if($canSwitchBranches ?? false)
    <li class="dropdown branch-switcher{{ ($isInBranchContext ?? false) ? ' branch-switcher--active' : '' }}">
        <a class="app-nav__item branch-switcher__toggle" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="{{ __('pages.branches.switch_branch') }}">
            <i class="fa fa-code-fork" aria-hidden="true"></i>
            <span class="branch-switcher__text d-none d-md-inline">
                {{ $activeBranchContext?->code ?? __('pages.shared.all_branches') }}
            </span>
            <i class="fa fa-angle-down branch-switcher__caret d-none d-md-inline" aria-hidden="true"></i>
        </a>
        <ul class="dropdown-menu dropdown-menu-right branch-switcher__menu">
            <li class="branch-switcher__heading">{{ __('pages.branches.switch_branch') }}</li>
            @foreach($branchSwitcherBranches as $switchBranch)
                @php
                    $isActiveBranch = ($activeBranchContext?->id === $switchBranch->id);
                    $branchLabel = $switchBranch->is_headquarters
                        ? __('pages.shared.headquarters')
                        : $switchBranch->name;
                @endphp
                <li>
                    <form action="{{ route('church.branches.enter', $switchBranch) }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="dropdown-item branch-switcher__item{{ $isActiveBranch ? ' active' : '' }}"
                                title="{{ $switchBranch->name }}">
                            <span class="branch-switcher__item-main">
                                <span class="branch-switcher__item-name">{{ $branchLabel }}</span>
                                @if($switchBranch->code)
                                    <span class="branch-switcher__item-code">{{ $switchBranch->code }}</span>
                                @endif
                            </span>
                            @if($switchBranch->is_headquarters)
                                <span class="branch-switcher__badge">HQ</span>
                            @endif
                        </button>
                    </form>
                </li>
            @endforeach
            @if($isInBranchContext ?? false)
                <li><hr class="dropdown-divider branch-switcher__divider"></li>
                <li>
                    <form action="{{ route('church.branches.exit') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item branch-switcher__item branch-switcher__exit">
                            <span class="branch-switcher__item-main">
                                <i class="fa fa-globe" aria-hidden="true"></i>
                                <span class="branch-switcher__item-name">{{ __('pages.branches.exit_all') }}</span>
                            </span>
                        </button>
                    </form>
                </li>
            @endif
        </ul>
    </li>
@endif
