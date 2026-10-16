<?php

namespace App\Http\Controllers\Api\Pastor;

use App\Http\Resources\Api\AnnouncementResource;
use App\Http\Resources\Api\ChurchServiceResource;
use App\Http\Resources\Api\SpecialEventResource;
use App\Services\Church\ChurchDashboardService;
use App\Support\ApiResponse;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;

class DashboardController extends PastorController
{
    public function __construct(
        private readonly ChurchDashboardService $churchDashboardService,
    ) {}

    public function index(): JsonResponse
    {
        $user = $this->pastor();
        $church = $this->church();
        $dashboard = $this->churchDashboardService->build($church, $user);

        $finance = null;
        if ($user->can('finance.view') && is_array($dashboard['finance'])) {
            $finance = $this->financePayload($dashboard['finance']);
        }

        return ApiResponse::success([
            'role' => [
                'label' => $dashboard['role_label'],
                'user_type' => $user->user_type?->value,
                'is_pastor' => $dashboard['is_pastor'],
                'is_assistant_pastor' => $dashboard['is_assistant_pastor'],
            ],
            'church' => [
                'id' => $church->id,
                'name' => $church->name,
                'email' => $church->email,
                'phone' => $church->phone,
                'currency' => $dashboard['currency'],
                'pastor_name' => $church->pastor_name,
            ],
            'active_branch' => $dashboard['active_branch'] ? [
                'id' => $dashboard['active_branch']->id,
                'name' => $dashboard['active_branch']->name,
            ] : null,
            'stats' => $dashboard['stats'],
            'ministry' => [
                'new_members_month' => $dashboard['stats']['new_members_month'],
                'children' => $dashboard['stats']['children'],
                'departments' => $dashboard['stats']['departments'],
                'leaders' => $dashboard['stats']['leaders'],
                'upcoming_services' => $dashboard['stats']['upcoming_services_count'],
                'upcoming_events' => $dashboard['stats']['upcoming_events_count'],
            ],
            'finance' => $finance,
            'pending_approvals' => $this->pendingApprovalSummary($dashboard['pending_approvals']),
            'announcements' => AnnouncementResource::collection($dashboard['announcements'])->resolve(),
            'upcoming_events' => SpecialEventResource::collection($dashboard['upcoming_events'])->resolve(),
            'upcoming_services' => ChurchServiceResource::collection($dashboard['upcoming_services'])->resolve(),
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $pending
     * @return array<string, mixed>|null
     */
    private function pendingApprovalSummary(?array $pending): ?array
    {
        if ($pending === null) {
            return null;
        }

        return [
            'counts' => $pending['counts'],
            'amounts' => $pending['amounts'],
            'total_pending' => $pending['total_pending'],
            'total_pending_amount' => $pending['total_pending_amount'],
        ];
    }

    /**
     * @param  array<string, mixed>  $finance
     * @return array<string, mixed>
     */
    private function financePayload(array $finance): array
    {
        return [
            'period' => $finance['period'],
            'summary' => $finance['summary'],
            'income_breakdown' => $finance['income_breakdown'],
            'recent_transactions' => collect($finance['recent_transactions'])->map(function (array $transaction) {
                $date = $transaction['date'] ?? null;

                return [
                    'type' => $transaction['type'] ?? null,
                    'label' => $transaction['label'] ?? null,
                    'member' => $transaction['member'] ?? null,
                    'description' => $transaction['description'] ?? null,
                    'amount' => (float) ($transaction['amount'] ?? 0),
                    'date' => $date instanceof CarbonInterface ? $date->toDateString() : ($date ? (string) $date : null),
                ];
            })->values()->all(),
        ];
    }
}
