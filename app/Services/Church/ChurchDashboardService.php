<?php

namespace App\Services\Church;

use App\Enums\MemberStatus;
use App\Enums\UserType;
use App\Models\Announcement;
use App\Models\AttendanceRecord;
use App\Models\Church;
use App\Models\ChurchService;
use App\Models\Department;
use App\Models\Expense;
use App\Models\Leader;
use App\Models\Member;
use App\Models\MemberDependant;
use App\Models\Offering;
use App\Models\SpecialEvent;
use App\Models\Tithe;
use App\Models\User;

class ChurchDashboardService
{
    public function __construct(
        private readonly FinanceDashboardService $financeDashboardService,
        private readonly FinanceApprovalService $financeApprovalService,
        private readonly MemberPortalService $memberPortalService,
        private readonly BranchAccessService $branchAccessService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(Church $church, User $user): array
    {
        $churchId = $church->id;
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $membersQuery = Member::forChurch($churchId);
        $this->branchAccessService->applyBranchScope($membersQuery, $user);

        $leadersQuery = Leader::forChurch($churchId)->active();
        $this->branchAccessService->applyBranchScope($leadersQuery, $user);

        $attendanceQuery = AttendanceRecord::forChurch($churchId)
            ->whereBetween('attended_at', [$monthStart, $monthEnd]);
        $this->branchAccessService->applyBranchScope($attendanceQuery, $user);

        $eventsQuery = SpecialEvent::forChurch($churchId)
            ->whereDate('event_date', '>=', now()->toDateString());
        $this->branchAccessService->applyBranchScope($eventsQuery, $user);

        $servicesQuery = ChurchService::forChurch($churchId)
            ->whereDate('service_date', '>=', now()->toDateString());
        $this->branchAccessService->applyBranchScope($servicesQuery, $user);

        $stats = [
            'total_members' => (clone $membersQuery)->count(),
            'active_members' => (clone $membersQuery)->where('status', MemberStatus::Active->value)->count(),
            'new_members_month' => (clone $membersQuery)->whereBetween('created_at', [$monthStart, $monthEnd])->count(),
            'children' => MemberDependant::forChurch($churchId)->count(),
            'departments' => tap(Department::forChurch($churchId), function ($query) use ($user) {
                $this->branchAccessService->applyBranchScope($query, $user);
            })->count(),
            'leaders' => (clone $leadersQuery)->count(),
            'monthly_attendance' => (clone $attendanceQuery)->count(),
            'upcoming_events_count' => (clone $eventsQuery)->count(),
            'upcoming_services_count' => (clone $servicesQuery)->count(),
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

            // Override period income/expense with branch-scoped sums when in a branch context.
            $effectiveBranchId = $this->branchAccessService->effectiveBranchId($user);
            if ($effectiveBranchId && $this->branchAccessService->branchesFeatureEnabled($user)) {
                $titheQuery = Tithe::forChurch($churchId)->approved()
                    ->whereMonth('tithe_date', now()->month)
                    ->whereYear('tithe_date', now()->year);
                $this->branchAccessService->applyBranchScope($titheQuery, $user);

                $offeringQuery = Offering::forChurch($churchId)->approved()
                    ->whereMonth('offering_date', now()->month)
                    ->whereYear('offering_date', now()->year);
                $this->branchAccessService->applyBranchScope($offeringQuery, $user);

                $expenseQuery = Expense::forChurch($churchId)
                    ->where('status', \App\Enums\ExpenseStatus::Paid->value)
                    ->whereMonth('expense_date', now()->month)
                    ->whereYear('expense_date', now()->year);
                $this->branchAccessService->applyBranchScope($expenseQuery, $user);

                $income = (float) $titheQuery->sum('amount') + (float) $offeringQuery->sum('amount');
                $expenses = (float) $expenseQuery->sum('amount');
                $stats['monthly_income'] = $income;
                $stats['monthly_expenses'] = $expenses;
                $stats['net_income'] = $income - $expenses;
            }
        }

        $pendingApprovals = $user->can('finance.approve')
            ? $this->financeApprovalService->buildDashboard($churchId)
            : null;

        $memberPortal = $user->hasLinkedMember()
            ? $this->memberPortalService->buildDashboard($user->member)
            : null;

        $announcements = $user->can('announcements.view')
            ? Announcement::forChurch($churchId)
                ->active()
                ->orderByDesc('is_pinned')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get()
            : collect();

        $upcomingEvents = $user->can('special_events.view')
            ? (clone $eventsQuery)->orderBy('event_date')->limit(5)->get()
            : collect();

        $upcomingServices = $user->can('services.view')
            ? (clone $servicesQuery)->orderBy('service_date')->limit(5)->get()
            : collect();

        return [
            'currency' => $church->currency ?? 'TZS',
            'role_label' => $user->churchRoleLabel(),
            'is_pastor' => in_array($user->user_type, [UserType::Pastor, UserType::AssistantPastor], true),
            'is_assistant_pastor' => $user->user_type === UserType::AssistantPastor,
            'is_elder' => $user->user_type === UserType::Elder,
            'is_secretary' => $user->user_type === UserType::Secretary,
            'is_treasurer' => $user->user_type === UserType::Treasurer,
            'is_accountant' => $user->user_type === UserType::Accountant,
            'is_administrator' => $user->user_type === UserType::ChurchAdmin,
            'stats' => $stats,
            'finance' => $finance,
            'pending_approvals' => $pendingApprovals,
            'member_portal' => $memberPortal,
            'announcements' => $announcements,
            'upcoming_events' => $upcomingEvents,
            'upcoming_services' => $upcomingServices,
            'active_branch' => $this->branchAccessService->activeBranch($user),
        ];
    }
}
