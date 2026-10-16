<?php

namespace App\Http\Controllers\Api\Pastor;

use App\Enums\MembershipType;
use App\Http\Requests\Api\Pastor\UpdatePastorBranchRequest;
use App\Http\Requests\Church\StoreBranchRequest;
use App\Http\Resources\Api\ChurchBranchResource;
use App\Models\ChurchBranch;
use App\Services\Church\BranchService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BranchController extends PastorController
{
    public function __construct(
        private readonly BranchService $branchService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ChurchBranch::class);

        $query = ChurchBranch::forChurch($this->pastor()->church_id)
            ->withCount([
                'members as members_count' => function ($q) {
                    $q->whereIn('membership_type', [
                        MembershipType::Permanent->value,
                        MembershipType::Temporary->value,
                    ]);
                },
                'leaders as leaders_count',
            ])
            ->orderByDesc('is_headquarters')
            ->orderBy('name');

        $status = trim($request->string('status')->toString());
        if ($status === 'inactive') {
            $query->where('is_active', false);
        } elseif ($status === 'all' || $request->boolean('include_inactive')) {
            // no status filter
        } else {
            $query->active();
        }

        $this->searchColumns($query, $request, ['name', 'code', 'pastor_name', 'city']);

        $branches = $query->paginate($this->perPage($request));

        return $this->paginated($branches, ChurchBranchResource::collection($branches)->resolve());
    }

    public function show(int $id): JsonResponse
    {
        $branch = $this->findBranch($id);
        if (! $branch) {
            return ApiResponse::error('Branch not found', 404);
        }

        $this->authorize('view', $branch);

        return ApiResponse::success((new ChurchBranchResource($branch))->resolve());
    }

    public function store(StoreBranchRequest $request): JsonResponse
    {
        $data = $request->validated();
        unset($data['logo']);

        $branch = $this->branchService->create(
            $this->church(),
            $data,
            $request->file('logo'),
        );

        $branch = $this->findBranch($branch->id) ?? $branch;

        return ApiResponse::success(
            (new ChurchBranchResource($branch))->resolve(),
            'Branch created successfully.',
            201,
        );
    }

    public function update(UpdatePastorBranchRequest $request, int $id): JsonResponse
    {
        $branch = $request->branchModel();
        if (! $branch) {
            return ApiResponse::error('Branch not found', 404);
        }

        $data = $request->validated();
        unset($data['logo']);

        $branch = $this->branchService->update(
            $branch,
            $data,
            $request->file('logo'),
        );

        $branch = $this->findBranch($branch->id) ?? $branch;

        return ApiResponse::success(
            (new ChurchBranchResource($branch))->resolve(),
            'Branch updated successfully.',
        );
    }

    private function findBranch(int $id): ?ChurchBranch
    {
        return ChurchBranch::forChurch($this->pastor()->church_id)
            ->withCount([
                'members as members_count' => function ($q) {
                    $q->whereIn('membership_type', [
                        MembershipType::Permanent->value,
                        MembershipType::Temporary->value,
                    ]);
                },
                'leaders as leaders_count',
            ])
            ->find($id);
    }
}
