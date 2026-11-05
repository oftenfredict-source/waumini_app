<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

class PageManual
{
    /**
     * Most-specific route patterns first.
     *
     * @var array<string, string>
     */
    private const ROUTE_MAP = [
        // Church — members
        'church.members.create' => 'members_register',
        'church.members.edit' => 'members_edit',
        'church.members.show' => 'members_show',
        'church.members.archived' => 'members_archived',
        'church.members.children.*' => 'members_children',
        'church.members.*' => 'members',
        'church.member-registrations.*' => 'member_registrations',
        'church.member-requests.*' => 'member_requests',

        // Church — core modules
        'church.dashboard' => 'dashboard',
        'church.branches.*' => 'branches',
        'church.leadership.*' => 'leadership',
        'church.departments.*' => 'departments',
        'church.announcements.*' => 'announcements',
        'church.services.*' => 'services',
        'church.special-events.*' => 'special_events',
        'church.celebrations.*' => 'celebrations',
        'church.promise-guests.*' => 'promise_guests',
        'church.attendance.*' => 'attendance',
        'church.bereavements.*' => 'bereavements',

        // Church — finance
        'church.finance.dashboard' => 'finance_dashboard',
        'church.finance.approvals' => 'finance_approvals',
        'church.tithes.*' => 'tithes',
        'church.offerings.*' => 'offerings',
        'church.pledges.*' => 'pledges',
        'church.budget.*' => 'budget',
        'church.assets.*' => 'assets',

        // Church — reports / system
        'church.analytics.*' => 'analytics',
        'church.reports.*' => 'reports',
        'church.system.settings.*' => 'system_settings',
        'church.system.users.*' => 'system_users',
        'church.system.roles.*' => 'system_roles',
        'church.system.sms.*' => 'system_sms',
        'church.system.subscription.*' => 'system_subscription',
        'church.system.support.*' => 'system_support',
        'church.system.logs.*' => 'system_logs',
        'church.system.sessions.*' => 'system_sessions',
        'church.system.monitor.*' => 'system_monitor',
        'church.system.otps.*' => 'system_otps',
        'church.system.*' => 'system',

        // Member portal
        'church.member.dashboard' => 'member_portal_dashboard',
        'church.member.profile.*' => 'member_portal_profile',
        'church.member.announcements.*' => 'member_portal_announcements',
        'church.member.requests.*' => 'member_portal_requests',
        'church.member.services.*' => 'member_portal_services',
        'church.member.leaders.*' => 'member_portal_leaders',
        'church.member.*' => 'member_portal',

        // Owner
        'owner.dashboard' => 'owner_dashboard',
        'owner.churches.*' => 'owner_churches',
        'owner.demo-requests.*' => 'owner_demo_requests',
        'owner.subscriptions.*' => 'owner_subscriptions',
        'owner.payments.*' => 'owner_payments',
        'owner.invoices.*' => 'owner_invoices',
        'owner.revenue.*' => 'owner_revenue',
        'owner.sms-usage.*' => 'owner_sms_usage',
        'owner.users.*' => 'owner_users',
        'owner.support.*' => 'owner_support',
        'owner.settings.*' => 'owner_settings',
        'owner.manual.*' => 'owner',
        'owner.*' => 'owner',

        // Manual pages themselves
        'church.manual.*' => 'church_default',
        'church.member.manual.*' => 'member_portal',
    ];

    public static function key(?string $routeName = null): string
    {
        $routeName = $routeName ?: Route::currentRouteName();

        if (! $routeName) {
            return 'default';
        }

        foreach (self::ROUTE_MAP as $pattern => $key) {
            if (self::matches($routeName, $pattern)) {
                return $key;
            }
        }

        if (str_starts_with($routeName, 'church.')) {
            return 'church_default';
        }

        if (str_starts_with($routeName, 'owner.')) {
            return 'owner_default';
        }

        return 'default';
    }

    /**
     * @return array{key: string, title: string, summary: string, steps: list<string>, tips: list<string>}
     */
    public static function forCurrentRoute(): array
    {
        return self::forKey(self::key());
    }

    /**
     * @return array{key: string, title: string, summary: string, steps: list<string>, tips: list<string>}
     */
    public static function forKey(string $key): array
    {
        $topic = __('manual.topics.'.$key);

        if (! is_array($topic) || empty($topic['title'])) {
            $topic = __('manual.topics.default');
        }

        return [
            'key' => $key,
            'title' => (string) ($topic['title'] ?? __('manual.fallback_title')),
            'summary' => (string) ($topic['summary'] ?? ''),
            'steps' => array_values(array_filter((array) ($topic['steps'] ?? []))),
            'tips' => array_values(array_filter((array) ($topic['tips'] ?? []))),
        ];
    }

    /**
     * @return list<array{key: string, title: string, summary: string}>
     */
    public static function catalog(string $scope = 'church'): array
    {
        $keys = match ($scope) {
            'owner' => [
                'owner_dashboard', 'owner_churches', 'owner_demo_requests', 'owner_subscriptions',
                'owner_payments', 'owner_invoices', 'owner_support', 'owner_settings',
            ],
            'member' => [
                'member_portal_dashboard', 'member_portal_profile', 'member_portal_announcements',
                'member_portal_requests', 'member_portal_services',
            ],
            default => [
                'dashboard', 'members', 'members_register', 'member_registrations', 'branches',
                'leadership', 'departments', 'announcements', 'services', 'attendance',
                'tithes', 'offerings', 'pledges', 'budget', 'assets', 'reports',
                'system_settings', 'system_sms', 'system_subscription', 'system_support',
            ],
        };

        $items = [];
        foreach ($keys as $key) {
            $manual = self::forKey($key);
            $items[] = [
                'key' => $key,
                'title' => $manual['title'],
                'summary' => $manual['summary'],
            ];
        }

        return $items;
    }

    private static function matches(string $routeName, string $pattern): bool
    {
        if (! str_contains($pattern, '*')) {
            return $routeName === $pattern;
        }

        $regex = '/^'.str_replace('\*', '.*', preg_quote($pattern, '/')).'$/';

        return (bool) preg_match($regex, $routeName);
    }
}
