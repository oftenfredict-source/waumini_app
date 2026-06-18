<?php

namespace App\Services\Church;

use App\Enums\MemberRequestStatus;
use App\Models\Announcement;
use App\Models\MemberRequest;
use App\Models\User;
use Illuminate\Support\Collection;

class HeaderNotificationService
{
    public function __construct(
        private readonly FinanceApprovalService $financeApprovalService,
    ) {}

    /**
     * @return array{count: int, items: Collection<int, array{title: string, message: string, meta: string, url: string, icon: string, icon_color: string}>}
     */
    public function forUser(User $user): array
    {
        $items = $user->isChurchMember()
            ? $this->memberNotifications($user)
            : ($user->hasLinkedMember()
                ? $this->staffNotifications($user)->merge($this->memberNotifications($user))
                : $this->staffNotifications($user));

        return [
            'count' => $items->count(),
            'items' => $items->take(10)->values(),
        ];
    }

    /**
     * @return Collection<int, array{title: string, message: string, meta: string, url: string, icon: string, icon_color: string}>
     */
    private function staffNotifications(User $user): Collection
    {
        $items = collect();
        $churchId = $user->church_id;

        if (! $churchId) {
            return $items;
        }

        if ($user->can('member_requests.view')) {
            MemberRequest::forChurch($churchId)
                ->with('member')
                ->whereIn('status', [
                    MemberRequestStatus::Pending->value,
                    MemberRequestStatus::InReview->value,
                ])
                ->latest()
                ->limit(5)
                ->get()
                ->each(function (MemberRequest $request) use ($items) {
                    $items->push([
                        'title' => 'Member request',
                        'message' => $request->subject ?: $request->type->label(),
                        'meta' => $request->member?->full_name.' · '.$request->created_at->diffForHumans(),
                        'url' => route('church.member-requests.show', $request),
                        'icon' => 'fa-envelope-open',
                        'icon_color' => 'text-warning',
                    ]);
                });
        }

        if ($user->can('finance.approve')) {
            $summary = $this->financeApprovalService->pendingSummary($churchId);

            if ($summary['count'] > 0) {
                $items->push([
                    'title' => 'Finance approvals',
                    'message' => $summary['count'].' item(s) awaiting your approval',
                    'meta' => number_format($summary['amount'], 2).' total',
                    'url' => route('church.finance.approvals'),
                    'icon' => 'fa-money',
                    'icon_color' => 'text-success',
                ]);
            }
        }

        if ($user->can('announcements.view')) {
            Announcement::forChurch($churchId)
                ->active()
                ->where('is_pinned', true)
                ->latest()
                ->limit(3)
                ->get()
                ->each(function (Announcement $announcement) use ($items) {
                    $items->push([
                        'title' => 'Announcement',
                        'message' => $announcement->title,
                        'meta' => $announcement->created_at->diffForHumans(),
                        'url' => route('church.announcements.show', $announcement),
                        'icon' => 'fa-bullhorn',
                        'icon_color' => 'text-primary',
                    ]);
                });
        }

        return $items;
    }

    /**
     * @return Collection<int, array{title: string, message: string, meta: string, url: string, icon: string, icon_color: string}>
     */
    private function memberNotifications(User $user): Collection
    {
        $items = collect();
        $member = $user->member;

        if (! $member) {
            return $items;
        }

        MemberRequest::forChurch($member->church_id)
            ->where('member_id', $member->id)
            ->whereIn('status', [
                MemberRequestStatus::Pending->value,
                MemberRequestStatus::InReview->value,
            ])
            ->latest()
            ->limit(3)
            ->get()
            ->each(function (MemberRequest $request) use ($items) {
                $items->push([
                    'title' => 'Request pending',
                    'message' => $request->subject ?: $request->type->label(),
                    'meta' => $request->status->label().' · '.$request->created_at->diffForHumans(),
                    'url' => route('church.member.requests.show', $request),
                    'icon' => 'fa-clock-o',
                    'icon_color' => 'text-warning',
                ]);
            });

        MemberRequest::forChurch($member->church_id)
            ->where('member_id', $member->id)
            ->whereIn('status', [
                MemberRequestStatus::Approved->value,
                MemberRequestStatus::Completed->value,
            ])
            ->where('responded_at', '>=', now()->subDays(14))
            ->latest('responded_at')
            ->limit(3)
            ->get()
            ->each(function (MemberRequest $request) use ($items) {
                $items->push([
                    'title' => 'Request '.$request->status->label(),
                    'message' => $request->subject ?: $request->type->label(),
                    'meta' => $request->responded_at?->diffForHumans() ?? $request->updated_at->diffForHumans(),
                    'url' => route('church.member.requests.show', $request),
                    'icon' => 'fa-check-circle',
                    'icon_color' => 'text-success',
                ]);
            });

        Announcement::forChurch($member->church_id)
            ->active()
            ->latest()
            ->limit(3)
            ->get()
            ->each(function (Announcement $announcement) use ($items) {
                $items->push([
                    'title' => 'Announcement',
                    'message' => $announcement->title,
                    'meta' => $announcement->created_at->diffForHumans(),
                    'url' => route('church.member.announcements.show', $announcement),
                    'icon' => 'fa-bullhorn',
                    'icon_color' => 'text-primary',
                ]);
            });

        return $items;
    }
}
