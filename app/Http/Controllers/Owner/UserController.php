<?php

namespace App\Http\Controllers\Owner;

use App\Enums\ChurchStatus;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $churchStaffTypes = array_map(
            fn (UserType $type) => $type->value,
            UserType::churchStaffTypes(),
        );

        $query = Church::query()
            ->with(['activeSubscription.package'])
            ->withCount([
                'users as staff_users_count' => fn ($q) => $q->whereIn('user_type', $churchStaffTypes),
                'members as members_count',
                'members as active_members_count' => fn ($q) => $q->activeMembers(),
            ])
            ->orderBy('name');

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($status = $request->string('status')->trim()->toString()) {
            $query->where('status', $status);
        }

        $churches = $query->paginate(20)->withQueryString();

        $platformStaff = User::query()
            ->whereIn('user_type', [UserType::Owner->value, UserType::Staff->value])
            ->with('roles')
            ->orderBy('name')
            ->get();

        return view('owner.users.index', [
            'churches' => $churches,
            'platformStaff' => $platformStaff,
            'filters' => $request->only(['search', 'status']),
            'statuses' => ChurchStatus::cases(),
            'overview' => [
                'churches' => Church::count(),
                'staff_users' => User::query()
                    ->whereNotNull('church_id')
                    ->whereIn('user_type', $churchStaffTypes)
                    ->count(),
                'active_members' => Member::query()->activeMembers()->count(),
                'platform_staff' => $platformStaff->count(),
            ],
        ]);
    }
}
