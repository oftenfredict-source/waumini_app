<?php

namespace App\Services\Church;

use App\Enums\MemberStatus;
use App\Enums\UserType;
use App\Models\Announcement;
use App\Models\AttendanceRecord;
use App\Models\Church;
use App\Models\ChurchService;
use App\Models\Department;
use App\Models\Leader;
use App\Models\Member;
use App\Models\MemberDependant;
use App\Models\SpecialEvent;
use App\Models\User;

class ChurchDashboardService
{
    public function __construct(
        private readonly FinanceDashboardService $financeDashboardService,
        private readonly FinanceApprovalService $financeApprovalService,
        private readonly MemberPortalService $memberPortalService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(Church $church, User $user): array
    {
        $churchId = $church->id;
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $stats = [
            'total_members' => Member::forChurch($churchId)->count(),
            'active_members' => Member::forChurch($churchId)->where('status', MemberStatus::Active->value)->count(),
            'new_members_month' => Member::forChurch($churchId)->whereBetween('created_at', [$monthStart, $monthEnd])->count(),
            'children' => MemberDependant::forChurch($churchId)->count(),
            'departments' => Department::forChurch($churchId)->count(),
            'leaders' => Leader::forChurch($churchId)->active()->count(),
            'monthly_attendance' => AttendanceRecord::forChurch($churchId)
                ->whereBetween('attended_at', [$monthStart, $monthEnd])
                ->count(),
            'upcoming_events_count' => SpecialEvent::forChurch($churchId)
                ->whereDate('event_date', '>=', now()->toDateString())
                ->count(),
            'upcoming_services_count' => ChurchService::forChurch($churchId)
                ->whereDate('service_date', '>=', now()->toDateString())
                ->count(),
        ];

        $finance = null;
        if ($user->can('finance.view')) {
            $finance = $this->financeDashboardService->build($church);
            $stats['monthly_income'] = $finance['summary']['total_income'];
            $stats['monthly_expenses'] = $finance['summary']['total_expenses'];
            $stats['net_income'] = $finance['summary']['net_balance'];
            $stats['pending_approvals'] = $finance['summary']['pending_approvals_count'];
            $stats['pending_approvals_amount'] = $finance['summary']['pending_approvals_amount'];
            $stats['active_pledges'] = $finance['summary']['active_pledges'];
            $stats['income_change_percent'] = $finance['summary']['income_change_percent'];
            $stats['expenses_year'] = $finance['summary']['expenses_year'];
        }

        $pendingApprovals = $user->can('finance.approve')
            ? $this->financeApprovalService->buildDashboard($churchId)
            : null;

        $memberPortal = $user->hasLinkedMember()
            ? $this->memberPortalService->buildDashboard($user->member)
            : null;

        return [
            'currency' => $church->currency ?? 'TZS',
            'role_label' => $user->churchRoleLabel(),
            'is_pastor' => $user->user_type === UserType::Pastor,
            'is_secretary' => $user->user_type === UserType::Secretary,
            'is_treasurer' => $user->user_type === UserType::Treasurer,
            'is_accountant' => $user->user_type === UserType::Accountant,
            'is_administrator' => $user->user_type === UserType::ChurchAdmin,
            'stats' => $stats,
            'finance' => $finance,
            'pending_approvals' => $pendingApprovals,
            'member_portal' => $memberPortal,
            'announcements' => $user->can('announcements.view')
                ? Announcement::forChurch($churchId)
                    ->active()
                    ->orderByDesc('is_pinned')
                    ->orderByDesc('created_at')
                    ->limit(5)
                    ->get()
                : collect(),
            'upcoming_events' => $user->can('special_events.view')
                ? SpecialEvent::forChurch($churchId)
                    ->whereDate('event_date', '>=', now()->toDateString())
                    ->orderBy('event_date')
                    ->limit(5)
                    ->get()
                : collect(),
            'upcoming_services' => $user->can('services.view')
                ? ChurchService::forChurch($churchId)
                    ->whereDate('service_date', '>=', now()->toDateString())
                    ->orderBy('service_date')
                    ->limit(5)
                    ->get()
                : collect(),
        ];
    }
}
