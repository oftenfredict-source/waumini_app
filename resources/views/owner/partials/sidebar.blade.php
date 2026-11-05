@php
    $items = [
        ['route' => 'owner.dashboard', 'match' => 'owner.dashboard', 'icon' => 'bi-speedometer2', 'label' => __('owner.overview')],
        ['route' => 'owner.churches.index', 'match' => 'owner.churches.*', 'icon' => 'bi-building', 'label' => __('owner.churches')],
        ['route' => 'owner.demo-requests.index', 'match' => 'owner.demo-requests.*', 'icon' => 'bi-person-check', 'label' => __('owner.demo_requests')],
        ['route' => 'owner.subscriptions.index', 'match' => 'owner.subscriptions.*', 'icon' => 'bi-credit-card', 'label' => __('owner.subscriptions')],
        ['route' => 'owner.payments.index', 'match' => 'owner.payments.*', 'icon' => 'bi-cash-coin', 'label' => __('owner.payments')],
        ['route' => 'owner.invoices.index', 'match' => 'owner.invoices.*', 'icon' => 'bi-receipt', 'label' => __('owner.invoices')],
        ['route' => 'owner.revenue.index', 'match' => 'owner.revenue.*', 'icon' => 'bi-graph-up', 'label' => __('owner.revenue')],
        ['route' => 'owner.sms-usage.index', 'match' => 'owner.sms-usage.*', 'icon' => 'bi-chat-dots', 'label' => __('owner.sms_usage')],
        ['route' => 'owner.users.index', 'match' => 'owner.users.*', 'icon' => 'bi-people', 'label' => __('owner.users_roles')],
        ['route' => 'owner.files.index', 'match' => 'owner.files.*', 'icon' => 'bi-folder2', 'label' => __('owner.files.menu')],
        ['route' => 'owner.support.index', 'match' => 'owner.support.*', 'icon' => 'bi-life-preserver', 'label' => __('owner.support')],
        ['route' => 'owner.settings.index', 'match' => 'owner.settings.*', 'icon' => 'bi-gear', 'label' => __('owner.settings')],
        ['route' => 'owner.manual.index', 'match' => 'owner.manual.*', 'icon' => 'bi-book', 'label' => __('menu.user_manual')],
    ];
@endphp

<aside class="admin-sidebar" id="admin-sidebar">
    <div class="sidebar-content">
        <nav class="sidebar-nav" aria-label="Owner navigation">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <span class="nav-section">{{ __('owner.overview') }}</span>
                </li>
                @foreach($items as $index => $item)
                    @if($index === 11)
                        <li class="nav-item">
                            <span class="nav-section">{{ __('owner.settings') }}</span>
                        </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs($item['match'])) active @endif"
                           href="{{ route($item['route']) }}">
                            <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </div>
</aside>
<div class="sidebar-backdrop" aria-hidden="true"></div>
