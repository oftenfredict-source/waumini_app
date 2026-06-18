@php
    $canSeeMenuItem = function (array $item) {
        if (empty($item['permission'])) {
            return true;
        }

        return auth()->user()->can($item['permission']);
    };

    $isMenuActive = function (array $item) {
        if (! empty($item['route']) && request()->routeIs($item['route'])) {
            return true;
        }

        if (! empty($item['active']) && request()->routeIs($item['active'])) {
            return true;
        }

        if (! empty($item['children'])) {
            foreach ($item['children'] as $child) {
                if (request()->routeIs($child['route'])) {
                    return true;
                }
            }
        }

        return false;
    };

    $currentSection = null;
    $church = auth()->user()->church;
    $branchesEnabled = (bool) ($church?->branches_enabled);
    $user = auth()->user();

    if ($user->isChurchMember()) {
        $menuItems = config('church.member_menu', []);
    } else {
        $menuItems = config('church.menu', []);

        if ($user->hasLinkedMember()) {
            $menuItems = array_merge($menuItems, config('church.personal_account_menu', []));
        }
    }
@endphp

<ul class="app-menu">
    @foreach($menuItems as $item)
        @php
            if (($item['feature'] ?? null) === 'branches' && ! $branchesEnabled) {
                continue;
            }

            $canAccess = empty($item['permission']) || $canSeeMenuItem($item);
        @endphp
        @if(! $canAccess)
            @continue
        @endif

        @php $section = $item['section'] ?? null; @endphp
        @if($section && $section !== $currentSection)
            <li class="app-menu__section">{{ strtoupper($section) }}</li>
            @php $currentSection = $section; @endphp
        @endif

        @if(! empty($item['children']))
            @php $visibleChildren = collect($item['children'])->filter($canSeeMenuItem); @endphp
            @if($visibleChildren->isEmpty())
                @continue
            @endif
            <li class="treeview {{ $isMenuActive($item) ? 'is-expanded' : '' }}">
                <a class="app-menu__item" href="#" data-toggle="treeview">
                    <i class="app-menu__icon fa {{ $item['icon'] }}"></i>
                    <span class="app-menu__label">{{ $item['label'] }}</span>
                    <i class="treeview-indicator fa fa-angle-right"></i>
                </a>
                <ul class="treeview-menu">
                    @foreach($visibleChildren as $child)
                        <li>
                            <a class="treeview-item {{ request()->routeIs($child['route']) ? 'active' : '' }}"
                               href="{{ route($child['route']) }}">
                                <i class="icon fa fa-circle-o"></i> {{ $child['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </li>
        @else
            <li>
                <a class="app-menu__item {{ $isMenuActive($item) ? 'active' : '' }}"
                   href="{{ route($item['route']) }}">
                    <i class="app-menu__icon fa {{ $item['icon'] }}"></i>
                    <span class="app-menu__label">{{ $item['label'] }}</span>
                </a>
            </li>
        @endif
    @endforeach
</ul>
