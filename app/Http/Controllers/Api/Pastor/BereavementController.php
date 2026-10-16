<?php

namespace App\Http\Controllers\Api\Pastor;

use App\Http\Requests\Api\Pastor\RecordPastorBereavementContributionRequest;
use App\Http\Requests\Church\StoreBereavementRequest;
use App\Http\Resources\Api\BereavementContributionResource;
use App\Http\Resources\Api\BereavementEventResource;
use App\Models\BereavementContribution;
use App\Models\BereavementEvent;
use App\Services\Church\BereavementService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BereavementController extends PastorController
{
    public function __construct(
        private readonly BereavementService $bereavementService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', BereavementEvent::class);

        $query = BereavementEvent::forChurch($this->pastor()->church_id)
            ->with('affectedMember:id,full_name,member_number,phone_number')
            ->latest('incident_date');

        if ($status = trim($request->string('status')->toString())) {
            $query->where('status', $status);
        }

        $this->searchColumns($query, $request, ['deceased_name', 'family_details']);

        $events = $query->paginate($this->perPage($request));

        return $this->paginated($events, BereavementEventResource::collection($events)->resolve());
    }

    public function show(int $id): JsonResponse
    {
        $event = $this->findEvent($id);
        if (! $event) {
            return ApiResponse::error('Bereavement not found', 404);
        }

        $this->authorize('view', $event);

        return ApiResponse::success((new BereavementEventResource($event))->resolve());
    }

    public function store(StoreBereavementRequest $request): JsonResponse
    {
        $event = $this->bereavementService->create(
            $this->church(),
            $request->validated(),
            $this->pastor(),
        )->load('affectedMember:id,full_name,member_number,phone_number');

        return ApiResponse::success(
            (new BereavementEventResource($event))->resolve(),
            'Bereavement event created successfully.',
            201,
        );
    }

    public function contributions(Request $request, int $id): JsonResponse
    {
        $event = $this->findEvent($id);
        if (! $event) {
            return ApiResponse::error('Bereavement not found', 404);
        }

        $this->authorize('view', $event);

        $query = BereavementContribution::query()
            ->where('bereavement_event_id', $event->id)
            ->with('member:id,full_name,member_number,envelope_number,phone_number');

        $status = trim($request->string('status')->toString());
        if ($status === 'contributed') {
            $query->where('has_contributed', true);
        } elseif ($status === 'pending') {
            $query->where('has_contributed', false);
        }

        if ($search = trim($request->string('search')->toString())) {
            $query->whereHas('member', function ($memberQuery) use ($search) {
                $memberQuery->where('full_name', 'like', "%{$search}%")
                    ->orWhere('member_number', 'like', "%{$search}%")
                    ->orWhere('envelope_number', 'like', "%{$search}%");
            });
        }

        $rows = $query->latest('id')->get()->sortBy(
            fn (BereavementContribution $row) => mb_strtolower($row->member?->full_name ?? '')
        )->values();

        $contributors = $rows->where('has_contributed', true)
            ->sortByDesc(fn (BereavementContribution $row) => $row->contribution_date?->toDateString().'-'.$row->id)
            ->values();
        $pending = $rows->where('has_contributed', false)->values();

        return ApiResponse::success([
            'contributors' => BereavementContributionResource::collection($contributors)->resolve(),
            'pending' => BereavementContributionResource::collection($pending)->resolve(),
            'total_contributions' => $event->totalContributions(),
            'contributors_count' => $event->contributorsCount(),
            'pending_count' => $event->pendingCount(),
            'can_manage_contributions' => $request->user()?->can('manageContributions', $event) ?? false,
        ]);
    }

    public function recordContribution(
        RecordPastorBereavementContributionRequest $request,
        int $id,
    ): JsonResponse {
        $event = $request->bereavementEvent();
        if (! $event) {
            return ApiResponse::error('Bereavement not found', 404);
        }

        $contribution = $this->bereavementService->recordContribution(
            $event,
            $request->validated(),
            $this->pastor(),
        );

        return ApiResponse::success(
            (new BereavementContributionResource($contribution))->resolve(),
            'Contribution recorded successfully.',
        );
    }

    public function markNonContributor(Request $request, int $id): JsonResponse
    {
        $event = $this->findEvent($id);
        if (! $event) {
            return ApiResponse::error('Bereavement not found', 404);
        }

        $this->authorize('manageContributions', $event);

        $data = $request->validate([
            'member_id' => [
                'required',
                Rule::exists('members', 'id')->where(
                    fn ($q) => $q->where('church_id', $this->pastor()->church_id)
                ),
            ],
        ]);

        $contribution = $this->bereavementService->markNonContributor(
            $event,
            (int) $data['member_id'],
            $this->pastor(),
        );

        return ApiResponse::success(
            (new BereavementContributionResource($contribution))->resolve(),
            'Contribution marked as pending.',
        );
    }

    private function findEvent(int $id): ?BereavementEvent
    {
        return BereavementEvent::forChurch($this->pastor()->church_id)
            ->with('affectedMember:id,full_name,member_number,phone_number')
            ->find($id);
    }
}
