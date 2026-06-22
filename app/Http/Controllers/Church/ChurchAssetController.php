<?php

namespace App\Http\Controllers\Church;

use App\Enums\AssetCategory;
use App\Enums\AssetCondition;
use App\Enums\AssetStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Church\StoreChurchAssetRequest;
use App\Http\Requests\Church\UpdateChurchAssetRequest;
use App\Models\ChurchAsset;
use App\Models\Member;
use App\Services\Church\BranchAccessService;
use App\Services\Church\ChurchAssetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChurchAssetController extends Controller
{
    public function __construct(
        private readonly ChurchAssetService $assetService,
        private readonly BranchAccessService $branchAccessService,
    ) {
        $this->authorizeResource(ChurchAsset::class, 'asset');
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $church = $user->church;

        $query = ChurchAsset::forChurch($church->id)
            ->with(['custodian', 'branch'])
            ->latest();

        $this->branchAccessService->applyBranchFilter(
            $query,
            $user,
            $request->integer('branch_id') ?: null,
        );

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('asset_tag', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($category = $request->string('category')->trim()->toString()) {
            $query->where('category', $category);
        }

        if ($status = $request->string('status')->trim()->toString()) {
            $query->where('status', $status);
        }

        $assets = $query->paginate(20)->withQueryString();

        return view('church.assets.index', [
            'assets' => $assets,
            'categories' => AssetCategory::cases(),
            'statuses' => AssetStatus::cases(),
            'filters' => $request->only(['search', 'category', 'status', 'branch_id']),
            'branches' => $this->branchAccessService->selectableBranches($user),
            'canFilterBranches' => $this->branchAccessService->branchesFeatureEnabled($user)
                && $this->branchAccessService->managesAllBranches($user),
        ]);
    }

    public function create(): View
    {
        return view('church.assets.create', $this->formData());
    }

    public function store(StoreChurchAssetRequest $request): RedirectResponse
    {
        $result = $this->assetService->register(
            $request->user()->church,
            $request->safe()->except(['photo']),
            $request->file('photo'),
            $request->user()->id,
        );

        $asset = $result['asset'];
        $count = $result['created_count'];

        if ($count > 1) {
            return redirect()
                ->route('church.assets.index', ['search' => $asset->name])
                ->with('success', "{$count} assets recorded successfully (tags {$result['first_tag']} to {$result['last_tag']}).");
        }

        return redirect()
            ->route('church.assets.show', $asset)
            ->with('success', "Asset \"{$asset->name}\" recorded with tag {$asset->asset_tag}.");
    }

    public function show(ChurchAsset $asset): View
    {
        $asset->load(['custodian', 'branch', 'recorder']);

        return view('church.assets.show', [
            'asset' => $asset,
        ]);
    }

    public function edit(ChurchAsset $asset): View
    {
        return view('church.assets.edit', array_merge(
            $this->formData(),
            ['asset' => $asset],
        ));
    }

    public function update(UpdateChurchAssetRequest $request, ChurchAsset $asset): RedirectResponse
    {
        $updated = $this->assetService->update(
            $asset,
            $request->safe()->except(['photo']),
            $request->file('photo'),
        );

        return redirect()
            ->route('church.assets.show', $updated)
            ->with('success', 'Asset updated successfully.');
    }

    public function destroy(ChurchAsset $asset): RedirectResponse
    {
        $name = $asset->name;
        $this->assetService->delete($asset);

        return redirect()
            ->route('church.assets.index')
            ->with('success', "Asset \"{$name}\" deleted successfully.");
    }

    /** @return array<string, mixed> */
    private function formData(): array
    {
        $user = auth()->user();
        $church = $user->church;

        return [
            'members' => Member::forChurch($church->id)
                ->activeMembers()
                ->orderBy('full_name')
                ->get(['id', 'full_name', 'member_number']),
            'branches' => $this->branchAccessService->selectableBranches($user),
            'defaultBranchId' => $this->branchAccessService->resolveBranchIdForCreate($user, null),
            'categories' => AssetCategory::cases(),
            'conditions' => AssetCondition::cases(),
            'statuses' => AssetStatus::cases(),
            'branchesEnabled' => $this->branchAccessService->branchesFeatureEnabled($user),
            'nextAssetTag' => $this->assetService->peekNextAssetTag($church),
        ];
    }
}
