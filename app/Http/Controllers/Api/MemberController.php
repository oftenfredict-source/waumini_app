<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\MemberIndexRequest;
use App\Http\Resources\Api\MemberDetailResource;
use App\Http\Resources\Api\MemberResource;
use App\Models\Member;
use App\Services\Church\BranchAccessService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class MemberController extends Controller
{
    public function __construct(
        private readonly BranchAccessService $branchAccessService,
    ) {}

    public function index(MemberIndexRequest $request): JsonResponse
    {
        $user = $request->user();
        $church = $user->church;

        $query = Member::forChurch($church->id)
            ->activeMembers()
            ->with(['branch:id,name'])
            ->latest();

        $this->branchAccessService->applyBranchFilter(
            $query,
            $user,
            $request->integer('branch_id') ?: null,
        );

        $this->applySearch($query, $request);

        $perPage = $request->integer('per_page') ?: 20;
        $members = $query->paginate($perPage);

        return ApiResponse::paginated(
            $members,
            MemberResource::collection($members)->resolve(),
        );
    }

    public function show(int $id): JsonResponse
    {
        $user = request()->user();

        $member = Member::forChurch($user->church_id)
            ->with(['branch:id,name'])
            ->find($id);

        if (! $member) {
            return ApiResponse::error('Member not found', 404);
        }

        $this->authorize('view', $member);

        return ApiResponse::success((new MemberDetailResource($member))->resolve());
    }

    private function applySearch($query, MemberIndexRequest $request): void
    {
        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
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
            $query->where(function ($q) use ($memberNumber) {
                $q->where('member_number', 'like', "%{$memberNumber}%")
                    ->orWhereRaw('LOWER(member_number) = ?', [strtolower($memberNumber)]);
            });
        }

        if ($phone = $request->string('phone')->trim()->toString()) {
            $query->where('phone_number', 'like', "%{$phone}%");
        }

        if ($email = $request->string('email')->trim()->toString()) {
            $query->where('email', 'like', "%{$email}%");
        }
    }
}
