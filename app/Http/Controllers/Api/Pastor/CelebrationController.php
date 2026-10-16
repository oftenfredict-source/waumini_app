<?php

namespace App\Http\Controllers\Api\Pastor;

use App\Enums\CelebrationStatus;
use App\Enums\CelebrationType;
use App\Http\Requests\Api\Pastor\StorePastorCelebrationRequest;
use App\Http\Resources\Api\CelebrationResource;
use App\Models\Celebration;
use App\Services\Church\CelebrationService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CelebrationController extends PastorController
{
    public function __construct(
        private readonly CelebrationService $celebrationService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Celebration::class);

        $church = $this->church();
        $this->celebrationService->syncForChurch($church);

        $year = $request->filled('year') ? $request->integer('year') : now()->year;
        $filter = trim($request->string('filter')->toString());
        $type = trim($request->string('celebration_type')->toString());

        [$period, $resolvedType] = $this->resolveFilter($filter, $type);

        $query = Celebration::forChurch($church->id)
            ->with($this->memberWithSpouse())
            ->forCelebrationYear($year)
            ->orderBy('celebration_date')
            ->orderBy('title');

        if ($status = trim($request->string('status')->toString())) {
            $query->where('status', $status);
        } else {
            $query->where('status', CelebrationStatus::Upcoming);
        }

        if ($resolvedType !== '') {
            $query->where('celebration_type', $resolvedType);
        }

        $query->notPassedYet($year);

        if ($period === 'this_month') {
            $query->inEventMonth((int) now()->month);
        } elseif ($period === 'next_30_days') {
            $query->withinDays(30);
        }

        $search = trim($request->string('search')->toString());
        if ($search !== '') {
            $query->where(function ($inner) use ($search) {
                $inner->where('title', 'like', "%{$search}%")
                    ->orWhere('venue', 'like', "%{$search}%")
                    ->orWhere('special_guest', 'like', "%{$search}%")
                    ->orWhereHas('member', function ($member) use ($search) {
                        $member->where('full_name', 'like', "%{$search}%")
                            ->orWhere('spouse_full_name', 'like', "%{$search}%")
                            ->orWhereHas(
                                'spouseMember',
                                fn ($spouse) => $spouse->where('full_name', 'like', "%{$search}%"),
                            );
                    });
            });
        }

        $celebrations = $query->paginate($this->perPage($request));

        return $this->paginated(
            $celebrations,
            CelebrationResource::collection($celebrations)->resolve(),
        );
    }

    public function store(StorePastorCelebrationRequest $request): JsonResponse
    {
        $celebration = $this->celebrationService->createManual(
            $this->church(),
            $request->validated(),
            $this->pastor(),
        )->load($this->memberWithSpouse());

        return ApiResponse::success(
            (new CelebrationResource($celebration))->resolve(),
            'Celebration created successfully.',
            201,
        );
    }

    public function show(int $id): JsonResponse
    {
        $celebration = Celebration::forChurch($this->pastor()->church_id)
            ->with($this->memberWithSpouse())
            ->find($id);

        if (! $celebration) {
            return ApiResponse::error('Celebration not found', 404);
        }

        $this->authorize('view', $celebration);

        return ApiResponse::success((new CelebrationResource($celebration))->resolve());
    }

    /**
     * @return array<int, string>
     */
    private function memberWithSpouse(): array
    {
        return [
            'member:id,full_name,member_number,spouse_full_name,spouse_member_id',
            'member.spouseMember:id,full_name',
            'member.spouseOf:id,full_name,spouse_member_id',
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function resolveFilter(string $filter, string $type): array
    {
        return match ($filter) {
            'this_month' => ['this_month', $type],
            'birthday' => ['all', CelebrationType::Birthday->value],
            'wedding_anniversary' => ['all', CelebrationType::WeddingAnniversary->value],
            'other' => ['all', CelebrationType::Other->value],
            'all' => ['all', $type],
            default => ['next_30_days', $type],
        };
    }
}
