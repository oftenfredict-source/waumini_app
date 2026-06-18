<?php

namespace App\Http\Controllers\Church;

use App\Http\Controllers\Controller;
use App\Http\Requests\Church\ConvertChildToMemberRequest;
use App\Http\Requests\Church\StoreChildRequest;
use App\Models\Member;
use App\Models\MemberDependant;
use App\Services\Church\MemberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberChildController extends Controller
{
    public function __construct(
        private readonly MemberService $memberService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', MemberDependant::class);

        $church = auth()->user()->church;
        $independenceAge = config('membership.child_independence_age', 21);

        $query = MemberDependant::forChurch($church->id)
            ->children()
            ->with(['member', 'linkedMember'])
            ->latest();

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('guardian_full_name', 'like', "%{$search}%")
                    ->orWhereHas('member', fn ($m) => $m->where('full_name', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->string('status')->trim()->toString()) {
            match ($status) {
                'converted' => $query->whereNotNull('linked_member_id'),
                'eligible' => $query->whereNull('linked_member_id')
                    ->whereNotNull('date_of_birth')
                    ->whereDate('date_of_birth', '<=', now()->subYears($independenceAge)->toDateString()),
                'active' => $query->whereNull('linked_member_id')
                    ->where(function ($q) use ($independenceAge) {
                        $q->whereNull('date_of_birth')
                            ->orWhereDate('date_of_birth', '>', now()->subYears($independenceAge)->toDateString());
                    }),
                default => null,
            };
        }

        $children = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => MemberDependant::forChurch($church->id)->children()->count(),
            'eligible' => MemberDependant::forChurch($church->id)->children()->eligibleForIndependence()->count(),
            'converted' => MemberDependant::forChurch($church->id)->children()->whereNotNull('linked_member_id')->count(),
        ];

        return view('church.members.children.index', [
            'children' => $children,
            'filters' => $request->only(['search', 'status']),
            'stats' => $stats,
            'independenceAge' => $independenceAge,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', MemberDependant::class);

        $church = auth()->user()->church;

        $members = Member::forChurch($church->id)
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'member_number']);

        return view('church.members.children.create', [
            'members' => $members,
            'selectedMemberId' => $request->integer('member_id') ?: old('member_id'),
        ]);
    }

    public function store(StoreChildRequest $request): RedirectResponse
    {
        $church = auth()->user()->church;
        $data = $request->safe()->except(['member_id', 'parent_type']);

        if ($request->validated('parent_type') === 'member') {
            $parent = Member::forChurch($church->id)
                ->whereKey($request->validated('member_id'))
                ->firstOrFail();

            $this->authorize('update', $parent);

            $child = $this->memberService->addChild($church, $data, $parent);

            $message = "{$child->full_name} has been added as a child of {$parent->full_name}.";
        } else {
            $child = $this->memberService->addChild($church, $data);

            $message = "{$child->full_name} has been added under guardian {$child->guardian_full_name}.";
        }

        return redirect()
            ->route('church.members.children.index')
            ->with('success', $message);
    }

    public function convert(ConvertChildToMemberRequest $request, MemberDependant $dependant): RedirectResponse
    {
        $this->authorize('convert', $dependant);

        $dependant->load('member');

        try {
            $member = $this->memberService->convertChildToIndependentMember(
                $dependant,
                $request->validated('envelope_number'),
                $request->validated('phone_number')
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $redirect = redirect()
            ->route('church.members.show', $member)
            ->with('success', "{$dependant->full_name} is now an independent member.");

        if (auth()->user()->canManageMemberPasswords()) {
            $redirect->with('registered_accounts', $this->memberService->getRegisteredAccounts());
        } elseif ($this->memberService->getRegisteredAccounts() !== []) {
            $redirect->with('info', 'Login account created. Contact your church administrator for member credentials.');
        }

        return $redirect;
    }

    public function processAgedOut(): RedirectResponse
    {
        $this->authorize('viewAny', MemberDependant::class);

        $church = auth()->user()->church;
        $converted = $this->memberService->processAgedOutChildren($church);

        if ($converted === 0) {
            return back()->with('error', 'No children aged '.config('membership.child_independence_age', 21).'+ are waiting for conversion with an available envelope.');
        }

        return back()->with('success', "{$converted} child(ren) converted to independent members.");
    }
}
