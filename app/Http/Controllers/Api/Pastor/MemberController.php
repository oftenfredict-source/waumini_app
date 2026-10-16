<?php

namespace App\Http\Controllers\Api\Pastor;

use App\Http\Requests\Api\MemberIndexRequest;
use App\Http\Requests\Api\Pastor\StorePastorMemberRequest;
use App\Http\Requests\Api\Pastor\UpdatePastorMemberRequest;
use App\Http\Requests\Church\StoreChildRequest;
use App\Http\Resources\Api\MemberDependantResource;
use App\Http\Resources\Api\MemberDetailResource;
use App\Http\Resources\Api\MemberResource;
use App\Models\Member;
use App\Models\MemberDependant;
use App\Services\Church\BranchAccessService;
use App\Services\Church\ChurchSettingsService;
use App\Services\Church\MemberService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MemberController extends PastorController
{
    public function __construct(
        private readonly BranchAccessService $branchAccessService,
        private readonly MemberService $memberService,
        private readonly ChurchSettingsService $churchSettingsService,
    ) {}

    public function index(MemberIndexRequest $request): JsonResponse
    {
        $user = $this->pastor();

        $query = Member::forChurch($user->church_id)
            ->with(['branch:id,name'])
            ->latest();

        if ($status = $request->string('status')->trim()->toString()) {
            $query->where('status', $status);
        } else {
            $query->activeMembers();
        }

        if ($membershipType = $request->string('membership_type')->trim()->toString()) {
            $query->where('membership_type', $membershipType);
        }

        $this->branchAccessService->applyBranchFilter(
            $query,
            $user,
            $request->integer('branch_id') ?: null,
        );

        $this->applySearch($query, $request);

        $members = $query->paginate($this->perPage($request));

        return $this->paginated($members, MemberResource::collection($members)->resolve());
    }

    public function checkEnvelope(Request $request): JsonResponse
    {
        $this->authorize('create', Member::class);

        $envelope = $request->string('envelope')->trim()->toString();
        $branchId = $this->branchAccessService->resolveBranchIdForCreate(
            $this->pastor(),
            $request->integer('branch_id') ?: null,
        );

        if (strlen($envelope) !== 3 || ! ctype_digit($envelope)) {
            return ApiResponse::success([
                'available' => false,
                'message' => 'Envelope number must be exactly 3 digits.',
            ]);
        }

        $exceptMemberId = $request->integer('except_member_id') ?: null;

        $available = $this->memberService->isEnvelopeAvailable(
            $this->church(),
            $envelope,
            $exceptMemberId,
            $branchId,
        );

        return ApiResponse::success([
            'available' => $available,
            'message' => $available
                ? 'Envelope number is available.'
                : 'Envelope number is already taken.',
        ]);
    }

    public function store(StorePastorMemberRequest $request): JsonResponse
    {
        $member = $this->memberService->create(
            $this->church(),
            $request->safe()->except(['profile_picture', 'dependants']),
            $request->file('profile_picture'),
            $request->input('dependants', []),
        );

        $member->load(['branch:id,name', 'departments', 'spouseMember', 'dependants']);

        return ApiResponse::success(
            (new MemberDetailResource($member))->resolve(),
            'Member registered successfully.',
            201,
        );
    }

    public function show(int $id): JsonResponse
    {
        $member = Member::forChurch($this->pastor()->church_id)
            ->with(['branch:id,name', 'departments', 'spouseMember', 'dependants'])
            ->find($id);

        if (! $member) {
            return ApiResponse::error('Member not found', 404);
        }

        $this->authorize('view', $member);

        return ApiResponse::success((new MemberDetailResource($member))->resolve());
    }

    public function update(UpdatePastorMemberRequest $request, int $id): JsonResponse
    {
        $member = Member::forChurch($this->pastor()->church_id)->find($id);

        if (! $member) {
            return ApiResponse::error('Member not found', 404);
        }

        $this->authorize('update', $member);

        $updated = $this->memberService->update(
            $member,
            $request->safe()->except(['profile_picture']),
            $request->file('profile_picture'),
        )->load(['branch:id,name', 'departments', 'spouseMember', 'dependants']);

        return ApiResponse::success(
            (new MemberDetailResource($updated))->resolve(),
            'Member updated successfully.',
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $member = Member::forChurch($this->pastor()->church_id)->find($id);

        if (! $member) {
            return ApiResponse::error('Member not found', 404);
        }

        $this->authorize('delete', $member);

        $name = $member->full_name;
        $this->memberService->deleteMember($member);

        return ApiResponse::success(
            ['id' => $id],
            "{$name} has been deleted.",
        );
    }

    public function storeChild(StoreChildRequest $request): JsonResponse
    {
        $church = $this->church();
        $data = $request->safe()->except(['member_id', 'parent_type']);

        if ($request->validated('parent_type') === 'member') {
            $parent = Member::forChurch($church->id)
                ->whereKey($request->validated('member_id'))
                ->firstOrFail();

            $this->authorize('update', $parent);
            $child = $this->memberService->addChild($church, $data, $parent);
        } else {
            $child = $this->memberService->addChild($church, $data);
        }

        $child->load('member:id,full_name,member_number,phone_number');

        return ApiResponse::success(
            (new MemberDependantResource($child))->resolve(),
            'Child registered successfully.',
            201,
        );
    }

    public function children(Request $request): JsonResponse
    {
        $this->authorize('viewAny', MemberDependant::class);

        $independenceAge = $this->churchSettingsService->childGraduationAge($this->church());
        $query = MemberDependant::forChurch($this->pastor()->church_id)
            ->children()
            ->with(['member:id,full_name,member_number,phone_number'])
            ->latest();

        $status = trim($request->string('status')->toString());
        match ($status) {
            'eligible' => $query->eligibleForIndependence($independenceAge),
            'converted' => $query->whereNotNull('linked_member_id'),
            'all' => $query,
            default => $query->activeChildren($independenceAge),
        };

        $search = trim($request->string('search')->toString());
        if ($search !== '') {
            $query->where(function ($inner) use ($search) {
                $inner->where('full_name', 'like', "%{$search}%")
                    ->orWhere('guardian_full_name', 'like', "%{$search}%")
                    ->orWhere('guardian_phone', 'like', "%{$search}%")
                    ->orWhereHas('member', fn ($member) => $member
                        ->where('full_name', 'like', "%{$search}%")
                        ->orWhere('member_number', 'like', "%{$search}%"));
            });
        }

        $children = $query->paginate($this->perPage($request));

        return $this->paginated($children, MemberDependantResource::collection($children)->resolve());
    }

    public function showChild(int $id): JsonResponse
    {
        $this->authorize('viewAny', MemberDependant::class);

        $child = MemberDependant::forChurch($this->pastor()->church_id)
            ->with([
                'member:id,full_name,member_number,phone_number',
                'linkedMember:id,full_name,member_number',
                'departments:id,name',
            ])
            ->find($id);

        if (! $child) {
            return ApiResponse::error('Child not found', 404);
        }

        return ApiResponse::success((new MemberDependantResource($child))->resolve());
    }

    private function applySearch($query, MemberIndexRequest $request): void
    {
        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($inner) use ($search) {
                $inner->where('full_name', 'like', "%{$search}%")
                    ->orWhere('member_number', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($name = $request->string('name')->trim()->toString()) {
            $query->where('full_name', 'like', "%{$name}%");
        }

        $memberNumber = $request->string('member_id')->trim()->toString()
            ?: $request->string('member_number')->trim()->toString();

        if ($memberNumber !== '') {
            $query->where('member_number', 'like', "%{$memberNumber}%");
        }

        if ($phone = $request->string('phone')->trim()->toString()) {
            $query->where('phone_number', 'like', "%{$phone}%");
        }

        if ($email = $request->string('email')->trim()->toString()) {
            $query->where('email', 'like', "%{$email}%");
        }
    }
}
