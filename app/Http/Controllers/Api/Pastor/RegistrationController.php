<?php

namespace App\Http\Controllers\Api\Pastor;

use App\Http\Resources\Api\MemberRegistrationResource;
use App\Models\MemberRegistrationApplication;
use App\Services\Church\BranchAccessService;
use App\Services\Church\MemberRegistrationApplicationService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RegistrationController extends PastorController
{
    public function __construct(
        private readonly BranchAccessService $branchAccessService,
        private readonly MemberRegistrationApplicationService $registrationService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', MemberRegistrationApplication::class);

        $user = $this->pastor();
        $query = MemberRegistrationApplication::forChurch($user->church_id)
            ->with('branch:id,name')
            ->latest();

        if ($status = trim($request->string('status')->toString())) {
            $query->where('status', $status);
        }

        $this->branchAccessService->applyBranchFilter(
            $query,
            $user,
            $request->integer('branch_id') ?: null,
        );
        $this->searchColumns($query, $request, ['application_number', 'full_name', 'phone_number']);

        $applications = $query->paginate($this->perPage($request));

        return $this->paginated($applications, MemberRegistrationResource::collection($applications)->resolve());
    }

    public function show(int $id): JsonResponse
    {
        $application = MemberRegistrationApplication::forChurch($this->pastor()->church_id)
            ->with('branch:id,name')
            ->find($id);

        if (! $application) {
            return ApiResponse::error('Registration application not found', 404);
        }

        $this->authorize('view', $application);

        return ApiResponse::success((new MemberRegistrationResource($application))->resolve());
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $application = MemberRegistrationApplication::forChurch($this->pastor()->church_id)->find($id);

        if (! $application) {
            return ApiResponse::error('Registration application not found', 404);
        }

        $this->authorize('review', $application);

        $validated = $request->validate([
            'envelope_number' => ['nullable', 'string', 'digits:3'],
            'spouse_envelope_number' => ['nullable', 'string', 'digits:3'],
        ]);

        try {
            $this->registrationService->approve(
                $application,
                $this->pastor(),
                $validated['envelope_number'] ?? null,
                $validated['spouse_envelope_number'] ?? null,
            );
        } catch (ValidationException $e) {
            return ApiResponse::error(
                collect($e->errors())->flatten()->first() ?: $e->getMessage(),
                422,
                ['errors' => $e->errors()],
            );
        }

        return ApiResponse::success(
            (new MemberRegistrationResource($application->fresh('branch:id,name')))->resolve(),
            'Registration approved successfully.',
        );
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $application = MemberRegistrationApplication::forChurch($this->pastor()->church_id)->find($id);

        if (! $application) {
            return ApiResponse::error('Registration application not found', 404);
        }

        $this->authorize('review', $application);

        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->registrationService->reject(
            $application,
            $this->pastor(),
            $validated['rejection_reason'] ?? null,
        );

        return ApiResponse::success(
            (new MemberRegistrationResource($application->fresh('branch:id,name')))->resolve(),
            'Registration rejected.',
        );
    }
}
