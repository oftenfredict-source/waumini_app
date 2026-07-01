@php
    $notificationCount = $headerNotifications['count'] ?? 0;
    $notificationItems = $headerNotifications['items'] ?? collect();
@endphp
<li class="dropdown app-nav__notification">
    <a class="app-nav__item" href="#" data-toggle="dropdown" aria-label="{{ __('common.notifications') }}">
        <i class="fa fa-bell-o fa-lg"></i>
        @if($notificationCount > 0)
            <span class="app-nav__notification-badge">{{ $notificationCount > 9 ? '9+' : $notificationCount }}</span>
        @endif
    </a>
    <ul class="app-notification dropdown-menu dropdown-menu-right">
        <li class="app-notification__title">
            @if($notificationCount > 0)
                {{ trans_choice('common.notifications_count', $notificationCount, ['count' => $notificationCount]) }}
            @else
                {{ __('common.no_new_notifications') }}
            @endif
        </li>
        <div class="app-notification__content">
            @forelse($notificationItems as $item)
                <li>
                    <a class="app-notification__item" href="{{ $item['url'] }}">
                        <span class="app-notification__icon">
                            <span class="fa-stack fa-lg">
                                <i class="fa fa-circle fa-stack-2x {{ $item['icon_color'] }}"></i>
                                <i class="fa {{ $item['icon'] }} fa-stack-1x fa-inverse"></i>
                            </span>
                        </span>
                        <div>
                            <p class="app-notification__message">{{ $item['title'] }}</p>
                            <p class="mb-0">{{ $item['message'] }}</p>
                            <p class="app-notification__meta">{{ $item['meta'] }}</p>
                        </div>
                    </a>
                </li>
            @empty
                <li class="px-3 py-2 text-muted text-center">{{ __('common.all_caught_up') }}</li>
            @endforelse
        </div>
    </ul>
</li>
