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
use App\Services\Church\DepartmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function __construct(
        private readonly DepartmentService $departmentService,
    ) {
        $this->authorizeResource(Department::class, 'department');
    }

    public function index(Request $request): View
    {
        $church = auth()->user()->church;

        $query = Department::forChurch($church->id)
            ->with('head')
            ->withCount('members')
            ->orderBy('name');

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

        $members = Member::forChurch($church->id)
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'member_number']);

        return view('church.departments.index', [
            'departments' => $departments,
            'members' => $members,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(): View
    {
        return view('church.departments.create', $this->formData());
    }

    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        $church = auth()->user()->church;
        $department = $this->departmentService->create($church, $request->validated());

        return redirect()
            ->route('church.departments.show', $department)
            ->with('success', 'Department created successfully.');
    }

    public function show(Department $department): View
    {
        $department->load(['head', 'members']);
        $church = auth()->user()->church;

        $availableMembers = Member::forChurch($church->id)
            ->where('status', 'active')
            ->whereNotIn('id', $department->members->pluck('id'))
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'member_number']);

        return view('church.departments.show', [
            'department' => $department,
            'availableMembers' => $availableMembers,
            'members' => Member::forChurch($church->id)
                ->where('status', 'active')
                ->orderBy('full_name')
                ->get(['id', 'full_name', 'member_number']),
        ]);
    }

    public function edit(Department $department): View
    {
        return view('church.departments.edit', array_merge(
            $this->formData(),
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

    /** @return array<string, mixed> */
    private function formData(): array
    {
        $church = auth()->user()->church;

        return [
            'members' => Member::forChurch($church->id)
                ->where('status', 'active')
                ->orderBy('full_name')
                ->get(['id', 'full_name', 'member_number']),
            'statuses' => DepartmentStatus::cases(),
        ];
    }
}
