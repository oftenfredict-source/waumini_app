<?php

namespace App\Http\Controllers\Church;

use App\Http\Controllers\Controller;
use App\Http\Requests\Church\StoreBranchRequest;
use App\Http\Requests\Church\UpdateBranchRequest;
use App\Models\ChurchBranch;
use App\Services\Church\BranchAccessService;
use App\Services\Church\BranchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function __construct(
        private readonly BranchService $branchService,
        private readonly BranchAccessService $branchAccessService,
    ) {
        $this->authorizeResource(ChurchBranch::class, 'branch');
    }

    public function index(Request $request): View
    {
        $user = auth()->user();
        $church = $user->church;

        $query = ChurchBranch::forChurch($church->id)
            ->withCount('members')
            ->orderByDesc('is_headquarters')
            ->orderBy('name');

        $this->branchAccessService->applyBranchFilter(
            $query,
            $user,
            $request->integer('branch_id') ?: null,
            'id',
        );

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($request->string('status')->toString() === 'inactive') {
            $query->where('is_active', false);
        } elseif ($request->string('status')->toString() === 'active') {
            $query->where('is_active', true);
        }

        return view('church.branches.index', [
            'branches' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['search', 'status', 'branch_id']),
            'canManageAll' => $this->branchAccessService->managesAllBranches($user),
        ]);
    }

    public function create(): View
    {
        return view('church.branches.create');
    }

    public function store(StoreBranchRequest $request): RedirectResponse
    {
        $branch = $this->branchService->create(
            auth()->user()->church,
            $this->validatedBranchData($request),
            $request->file('logo'),
        );

        return redirect()
            ->route('church.branches.show', $branch)
            ->with('success', 'Branch created successfully.');
    }

    public function show(ChurchBranch $branch): View
    {
        $branch->loadCount(['members', 'leaders']);

        return view('church.branches.show', compact('branch'));
    }

    public function edit(ChurchBranch $branch): View
    {
        return view('church.branches.edit', compact('branch'));
    }

    public function update(UpdateBranchRequest $request, ChurchBranch $branch): RedirectResponse
    {
        $this->branchService->update(
            $branch,
            $this->validatedBranchData($request),
            $request->file('logo'),
        );

        return redirect()
            ->route('church.branches.show', $branch)
            ->with('success', 'Branch details saved successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedBranchData(StoreBranchRequest|UpdateBranchRequest $request): array
    {
        return array_merge($request->validated(), [
            'is_headquarters' => $request->boolean('is_headquarters'),
            'is_active' => $request->boolean('is_active', true),
            'remove_logo' => $request->boolean('remove_logo'),
        ]);
    }
}
