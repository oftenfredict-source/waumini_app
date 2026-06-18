<?php

namespace App\Http\Controllers\Church\System;

use App\Enums\ChurchStaffRole;
use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Models\User;
use App\Services\Church\ChurchStaffUserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends SystemController
{
    public function __construct(
        private readonly ChurchStaffUserService $churchStaffUserService,
    ) {
        $this->middleware(function ($request, $next) {
            abort_unless($request->user()?->can('system.users'), 403);

            return $next($request);
        });
    }

    public function index(): View
    {
        $church = $this->church();
        $staffTypes = array_map(fn (UserType $type) => $type->value, UserType::churchStaffTypes());

        $users = User::query()
            ->with('roles')
            ->where('church_id', $church->id)
            ->whereIn('user_type', $staffTypes)
            ->latest()
            ->paginate(15);

        return view('church.system.users.index', compact('church', 'users'));
    }

    public function create(): View
    {
        $church = $this->church();

        return view('church.system.users.create', [
            'church' => $church,
            'roles' => ChurchStaffRole::cases(),
            'branches' => $church->branches_enabled
                ? \App\Models\ChurchBranch::forChurch($church->id)->active()->orderBy('name')->get()
                : collect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $church = $this->church();
        $data = $this->validatedUserData($request, $church->id);

        $result = $this->churchStaffUserService->create($church, $data);

        return redirect()
            ->route('church.system.users.index')
            ->with('success', 'Staff user created successfully.')
            ->with('staff_credentials', [
                'email' => $result['user']->email,
                'password' => $result['password'],
            ]);
    }

    public function edit(User $user): View
    {
        $church = $this->church();
        $this->churchStaffUserService->ensureChurchStaff($church, $user);

        return view('church.system.users.edit', [
            'church' => $church,
            'user' => $user->load('roles'),
            'roles' => ChurchStaffRole::cases(),
            'branches' => $church->branches_enabled
                ? \App\Models\ChurchBranch::forChurch($church->id)->active()->orderBy('name')->get()
                : collect(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $church = $this->church();
        $this->churchStaffUserService->ensureChurchStaff($church, $user);

        $data = $this->validatedUserData($request, $church->id, $user->id);
        $this->churchStaffUserService->update($church, $user, $data);

        return redirect()
            ->route('church.system.users.index')
            ->with('success', 'Staff user updated successfully.');
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $church = $this->church();
        $this->churchStaffUserService->ensureChurchStaff($church, $user);

        $password = $this->churchStaffUserService->resetPassword($church, $user);

        return back()
            ->with('success', 'Password reset successfully. An SMS was sent to the user\'s phone if a number is on file.')
            ->with('staff_credentials', [
                'email' => $user->email,
                'password' => $password,
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedUserData(Request $request, int $churchId, ?int $userId = null): array
    {
        $roleValues = array_map(fn (ChurchStaffRole $role) => $role->value, ChurchStaffRole::cases());
        $church = \App\Models\Church::findOrFail($churchId);

        $rules = [
            'name' => ['required', 'string', 'max:150'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'role' => ['required', Rule::in($roleValues)],
            'status' => ['nullable', Rule::in(array_map(fn (UserStatus $s) => $s->value, UserStatus::cases()))],
        ];

        if ($church->branches_enabled) {
            $rules['branch_id'] = [
                'nullable',
                'integer',
                Rule::exists('church_branches', 'id')->where(fn ($q) => $q->where('church_id', $churchId)),
            ];
        }

        return $request->validate($rules) + [
            'status' => $request->input('status', UserStatus::Active->value),
            'branch_id' => $church->branches_enabled ? $request->input('branch_id') : null,
        ];
    }
}
