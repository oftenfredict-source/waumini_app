<?php

namespace App\Http\Controllers\Church;

use App\Enums\DepartmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Church\AssignDepartmentHeadRequest;
use App\Http\Requests\Church\StoreDepartmentRequest;
use App\Http\Requests\Church\SyncDepartmentMembersRequest;
use App\Http\Requests\Church\UpdateDepartmentRequest;
use App\Models\Department;
use App\Models\Member;
use App\Models\MemberDependant;
use App\Services\Church\BranchAccessService;
use App\Services\Church\DepartmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function __construct(
        private readonly DepartmentService $departmentService,
        private readonly BranchAccessService $branchAccessService,
    ) {
        $this->authorizeResource(Department::class, 'department');
    }

    public function index(Request $request): View
    {
        $user = auth()->user();
        $church = $user->church;

        $query = Department::forChurch($church->id)
            ->with(['head', 'branch'])
            ->withCount([
                'members as members_count' => fn ($q) => $q->activeMembers(),
                'dependants',
            ])
            ->orderBy('name');

        $this->branchAccessService->applyBranchFilter(
            $query,
            $user,
            $request->integer('branch_id') ?: null,
        );

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($status = $request->string('status')->trim()->toString()) {
            $query->where('status', $status);
        }

        $departments = $query->paginate(15)->withQueryString();

        $membersQuery = Member::forChurch($church->id)
            ->where('status', 'active')
            ->orderBy('full_name');
        $this->branchAccessService->applyBranchScope($membersQuery, $user);

        return view('church.departments.index', [
            'departments' => $departments,
            'members' => $membersQuery->get(['id', 'full_name', 'member_number']),
            'filters' => $request->only(['search', 'status', 'branch_id']),
            'branches' => $this->branchAccessService->selectableBranches($user),
            'canFilterBranches' => $this->branchAccessService->branchesFeatureEnabled($user)
                && $this->branchAccessService->managesAllBranches($user)
                && ! $this->branchAccessService->sessionBranchId($user),
            'branchesEnabled' => $this->branchAccessService->branchesFeatureEnabled($user),
        ]);
    }

    public function create(): View
    {
        return view('church.departments.create', $this->formData());
    }

    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        $church = auth()->user()->church;
        $department = $this->departmentService->create(
            $church,
            $request->validated(),
            $request->user(),
        );

        return redirect()
            ->route('church.departments.show', $department)
            ->with('success', 'Department created successfully.');
    }

    public function show(Department $department): View
    {
        $department->load([
            'head',
            'branch',
            'members' => fn ($q) => $q->activeMembers(),
            'dependants.member',
        ]);
        $church = auth()->user()->church;

        $availableMembersQuery = Member::forChurch($church->id)
            ->where('status', 'active')
            ->whereNotIn('id', $department->members->pluck('id'))
            ->orderBy('full_name');

        $membersQuery = Member::forChurch($church->id)
            ->where('status', 'active')
            ->orderBy('full_name');

        if ($department->branch_id) {
            $availableMembersQuery->where('branch_id', $department->branch_id);
            $membersQuery->where('branch_id', $department->branch_id);
        }

        return view('church.departments.show', [
            'department' => $department,
            'availableMembers' => $availableMembersQuery->get(['id', 'full_name', 'member_number']),
            'members' => $membersQuery->get(['id', 'full_name', 'member_number']),
            'branchesEnabled' => $this->branchAccessService->branchesFeatureEnabled(auth()->user()),
        ]);
    }

    public function edit(Department $department): View
    {
        return view('church.departments.edit', array_merge(
            $this->formData($department),
            ['department' => $department],
        ));
    }

    public function update(UpdateDepartmentRequest $request, Department $department): RedirectResponse
    {
        $this->departmentService->update($department, $request->validated());

        return redirect()
            ->route('church.departments.show', $department)
            ->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $name = $department->name;
        $this->departmentService->delete($department);

        return redirect()
            ->route('church.departments.index')
            ->with('success', "Department \"{$name}\" deleted successfully.");
    }

    public function assignHead(AssignDepartmentHeadRequest $request, Department $department): RedirectResponse
    {
        $this->authorize('update', $department);

        $headId = $request->validated('head_id');
        $this->departmentService->assignHead($department, $headId ? (int) $headId : null);

        $message = $headId
            ? 'Department leader assigned successfully.'
            : 'Department leader removed successfully.';

        return back()->with('success', $message);
    }

    public function attachMembers(SyncDepartmentMembersRequest $request, Department $department): RedirectResponse
    {
        $this->authorize('update', $department);

        $memberIds = $request->validated('member_ids', []);
        $attached = $this->departmentService->attachMembers($department, $memberIds);

        if ($attached === 0) {
            return back()->with('info', 'No new members were added.');
        }

        return back()->with('success', "{$attached} member(s) added to {$department->name}.");
    }

    public function removeMember(Department $department, Member $member): RedirectResponse
    {
        $this->authorize('update', $department);

        abort_unless($member->church_id === $department->church_id, 404);
        abort_unless($department->members()->where('member_id', $member->id)->exists(), 404);

        $this->departmentService->removeMember($department, $member);

        return back()->with('success', "{$member->full_name} removed from {$department->name}.");
    }

    public function removeDependant(Department $department, MemberDependant $dependant): RedirectResponse
    {
        $this->authorize('update', $department);

        abort_unless($dependant->church_id === $department->church_id, 404);
        abort_unless($department->dependants()->where('member_dependant_id', $dependant->id)->exists(), 404);

        $this->departmentService->removeDependant($department, $dependant);

        return back()->with('success', "{$dependant->full_name} removed from {$department->name}.");
    }

    /** @return array<string, mixed> */
    private function formData(?Department $department = null): array
    {
        $user = auth()->user();
        $church = $user->church;
        $defaultBranchId = $department?->branch_id
            ?? $this->branchAccessService->resolveBranchIdForCreate($user, null);

        $membersQuery = Member::forChurch($church->id)
            ->where('status', 'active')
            ->orderBy('full_name');

        if ($defaultBranchId) {
            $membersQuery->where('branch_id', $defaultBranchId);
        } else {
            $this->branchAccessService->applyBranchScope($membersQuery, $user);
        }

        return [
            'members' => $membersQuery->get(['id', 'full_name', 'member_number']),
            'statuses' => DepartmentStatus::cases(),
            'branches' => $this->branchAccessService->selectableBranches($user),
            'defaultBranchId' => $defaultBranchId,
            'canSelectBranch' => $this->branchAccessService->branchesFeatureEnabled($user)
                && $this->branchAccessService->managesAllBranches($user)
                && ! $this->branchAccessService->sessionBranchId($user)
                && $department === null,
            'branchesEnabled' => $this->branchAccessService->branchesFeatureEnabled($user),
        ];
    }
}
