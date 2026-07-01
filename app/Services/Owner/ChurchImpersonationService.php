<?php

namespace App\Services\Owner;

use App\Models\Church;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChurchImpersonationService
{
    public const SESSION_OWNER_ID = 'owner_impersonator_id';

    public const SESSION_CHURCH_ID = 'owner_impersonator_church_id';

    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    public function isActive(Request $request): bool
    {
        return $request->session()->has(self::SESSION_OWNER_ID);
    }

    public function ownerId(Request $request): ?int
    {
        $id = $request->session()->get(self::SESSION_OWNER_ID);

        return is_numeric($id) ? (int) $id : null;
    }

    public function start(User $owner, Church $church, Request $request): RedirectResponse
    {
        $admin = $church->adminUser;

        if (! $admin) {
            return redirect()
                ->back()
                ->with('error', __('owner.church.impersonate_no_admin'));
        }

        if ($admin->status->value !== 'active') {
            return redirect()
                ->back()
                ->with('error', __('owner.church.impersonate_admin_inactive'));
        }

        $this->auditLogService->log(
            'owner.church.impersonation.started',
            $church,
            null,
            [
                'owner_id' => $owner->id,
                'owner_email' => $owner->email,
                'church_admin_id' => $admin->id,
            ],
            $church->id,
        );

        $request->session()->put([
            self::SESSION_OWNER_ID => $owner->id,
            self::SESSION_CHURCH_ID => $church->id,
        ]);

        Auth::login($admin, remember: false);
        $request->session()->regenerate();

        $admin->update(['last_login_at' => now()]);

        return redirect()
            ->route('church.dashboard')
            ->with('success', __('owner.church.impersonate_started', ['church' => $church->name]));
    }

    public function stop(Request $request): RedirectResponse
    {
        $ownerId = $request->session()->pull(self::SESSION_OWNER_ID);
        $churchId = $request->session()->pull(self::SESSION_CHURCH_ID);

        $owner = is_numeric($ownerId) ? User::find((int) $ownerId) : null;
        $church = is_numeric($churchId) ? Church::find((int) $churchId) : null;

        if ($owner && $church) {
            $this->auditLogService->log(
                'owner.church.impersonation.ended',
                $church,
                null,
                [
                    'owner_id' => $owner->id,
                    'owner_email' => $owner->email,
                ],
                $church->id,
            );
        }

        Auth::logout();

        if ($owner && $owner->isOwnerUser()) {
            Auth::login($owner, remember: false);
            $request->session()->regenerate();

            if ($church) {
                return redirect()
                    ->route('owner.churches.show', $church)
                    ->with('success', __('owner.church.impersonate_ended'));
            }

            return redirect()
                ->route('owner.dashboard')
                ->with('success', __('owner.church.impersonate_ended'));
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('owner.login');
    }
}
