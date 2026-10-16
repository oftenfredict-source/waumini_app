<?php

namespace App\Http\Controllers\Api\Pastor;

use App\Http\Requests\Api\Pastor\AssignPastorDepartmentLeaderRequest;
use App\Http\Requests\Api\Pastor\AttachPastorDepartmentMembersRequest;
use App\Http\Requests\Church\StoreDepartmentRequest;
use App\Http\Resources\Api\DepartmentResource;
use App\Models\Department;
use App\Models\Member;
use App\Services\Church\BranchAccessService;
use App\Services\Church\DepartmentService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends PastorController
{
    public function __construct(
        private readonly BranchAccessService $branchAccessService,
        private readonly DepartmentService $departmentService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Department::class);

        $user = $this->pastor();
        $query = Department::forChurch($user->church_id)
            ->with(['head:id,full_name,member_number,phone_number', 'branch:id,name'])
            ->withCount(['members', 'dependants'])
            ->orderBy('name');

        if ($status = trim($request->string('status')->toString())) {
            $query->where('status', $status);
        }

        $this->branchAccessService->applyBranchFilter(
            $query,
            $user,
            $request->integer('branch_id') ?: null,
        );
        $this->searchColumns($query, $request, ['name', 'description']);

        $departments = $query->paginate($this->perPage($request));

        return $this->paginated($departments, DepartmentResource::collection($departments)->resolve());
    }

    public function show(int $id): JsonResponse
    {
        $department = $this->departmentRecord($id);

        if (! $department) {
            return ApiResponse::error('Department not found', 404);
        }

        $this->authorize('view', $department);

        return ApiResponse::success($this->payload($department));
    }

    public function availableMembers(Request $request, int $id): JsonResponse
    {
        $department = $this->departmentRecord($id);
        if (! $department) {
            return ApiResponse::error('Department not found', 404);
        }

        $this->authorize('update', $department);

        $query = Member::forChurch($this->pastor()->church_id)
            ->activeMembers()
            ->orderBy('full_name');

        if ($department->branch_id) {
            $query->where('branch_id', $department->branch_id);
        }

        $forLeader = $request->string('for')->toString() === 'leader';
        if (! $forLeader) {
            $assignedIds = $department->members()->pluck('members.id');
            if ($assignedIds->isNotEmpty()) {
                $query->whereNotIn('id', $assignedIds);
            }
        }

        $search = trim($request->string('search')->toString());
        if ($search !== '') {
            $query->where(function ($inner) use ($search) {
                $inner->where('full_name', 'like', "%{$search}%")
                    ->orWhere('member_number', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        $members = $query
            ->limit(300)
            ->get(['id', 'full_name', 'member_number', 'phone_number']);

        return ApiResponse::success($members->map(fn (Member $member) => [
            'id' => $member->id,
            'full_name' => $member->full_name,
            'member_number' => $member->member_number,
            'phone_number' => $member->phone_number,
        ])->values()->all());
    }

    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $department = $this->departmentService->create(
            $this->church(),
            $request->validated(),
            $this->pastor(),
        );

        return ApiResponse::success(
            $this->payload($department),
            'Department created successfully.',
            201,
        );
    }

    public function attachMembers(AttachPastorDepartmentMembersRequest $request, int $id): JsonResponse
    {
        $department = $request->department() ?? $this->departmentRecord($id);
        if (! $department) {
            return ApiResponse::error('Department not found', 404);
        }

        $memberIds = $request->validated('member_ids', []);
        $attached = $this->departmentService->attachMembers($department, $memberIds);

        return ApiResponse::success(
            $this->payload($department->fresh()),
            $attached === 0
                ? 'No new members were added.'
                : $attached.' member(s) added to '.$department->name.'.',
        );
    }

    public function assignLeader(AssignPastorDepartmentLeaderRequest $request, int $id): JsonResponse
    {
        $department = $request->department() ?? $this->departmentRecord($id);
        if (! $department) {
            return ApiResponse::error('Department not found', 404);
        }

        $data = $request->validated();
        $department = $this->departmentService->assignOfficer(
            $department,
            (int) $data['member_id'],
            (string) $data['role'],
        );

        return ApiResponse::success(
            $this->payload($department),
            'Department leader assigned successfully.',
        );
    }

    public function removeMember(int $id, int $memberId): JsonResponse
    {
        $department = $this->departmentRecord($id);
        if (! $department) {
            return ApiResponse::error('Department not found', 404);
        }

        $this->authorize('update', $department);

        $member = Member::forChurch($this->pastor()->church_id)->find($memberId);
        if (! $member || ! $department->members()->where('member_id', $member->id)->exists()) {
            return ApiResponse::error('Member not found in this department', 404);
        }

        $this->departmentService->removeMember($department, $member);

        return ApiResponse::success(
            $this->payload($department->fresh()),
            $member->full_name.' removed from '.$department->name.'.',
        );
    }

    private function departmentRecord(int $id): ?Department
    {
        return Department::forChurch($this->pastor()->church_id)->find($id);
    }

    /** @return array<string, mixed> */
    private function payload(Department $department): array
    {
        $department->load([
            'head:id,full_name,member_number,phone_number',
            'branch:id,name',
            'members' => fn ($q) => $q->orderBy('full_name'),
            'dependants.member:id,full_name',
        ])->loadCount(['members', 'dependants']);

        return (new DepartmentResource($department))->resolve();
    }
}
