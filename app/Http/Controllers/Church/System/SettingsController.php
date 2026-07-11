<?php

namespace App\Http\Controllers\Church\System;

use App\Enums\DepartmentStatus;
use App\Enums\LeadershipPosition;
use App\Models\Department;
use App\Models\SystemSetting;
use App\Services\Church\ChurchSettingsService;
use App\Services\Church\DepartmentAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends SystemController
{
    public function __construct(
        private readonly ChurchSettingsService $churchSettingsService,
        private readonly DepartmentAssignmentService $departmentAssignmentService,
    ) {
        $this->middleware(function ($request, $next) {
            abort_unless($request->user()?->can('system.settings'), 403);

            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        $church = $this->church();
        $tab = $request->string('tab')->trim()->toString() ?: 'general';

        if (! array_key_exists($tab, config('church_settings.categories', []))) {
            $tab = 'general';
        }

        $departments = Department::forChurch($church->id)
            ->where('status', DepartmentStatus::Active)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('church.system.settings.index', [
            'church' => $church,
            'tab' => $tab,
            'settings' => $this->churchSettingsService->all($church),
            'categories' => config('church_settings.categories'),
            'platformSenderId' => SystemSetting::smsGatewayConfig()['sender_id'],
            'departments' => $departments,
            'leadershipPositions' => LeadershipPosition::options(),
        ]);
    }

    public function update(Request $request, string $tab): RedirectResponse
    {
        $church = $this->church();
        $data = $this->churchSettingsService->validateTab($tab, $request->all(), $request, $church);
        $this->churchSettingsService->updateTab($church, $tab, $data, $request);

        $label = config("church_settings.categories.{$tab}.name", ucfirst($tab));
        $message = "{$label} settings saved successfully.";

        if ($tab === 'membership' && $request->boolean('sync_existing_members_on_save')) {
            $result = $this->departmentAssignmentService->syncExistingMembers($church->fresh());
            $message .= ' '.$this->syncResultMessage($result);
        }

        return redirect()
            ->route('church.system.settings.index', ['tab' => $tab])
            ->with('success', $message);
    }

    public function syncDepartmentAssignments(): RedirectResponse
    {
        $church = $this->church();
        $result = $this->departmentAssignmentService->syncExistingMembers($church);

        $redirect = redirect()->route('church.system.settings.index', ['tab' => 'membership']);

        if (! $result['enabled']) {
            return $redirect->with('error', 'Enable automatic department assignment and save at least one rule before syncing.');
        }

        return $redirect->with('success', $this->syncResultMessage($result));
    }

    /**
     * @param  array{scanned: int, matched: int, attached: int, removed?: int, enabled: bool}  $result
     */
    private function syncResultMessage(array $result): string
    {
        if ($result['scanned'] === 0) {
            return 'No active members or children found to sync.';
        }

        $message = sprintf(
            'Synced existing people: %d checked, %d matched, %d new assignment(s).',
            $result['scanned'],
            $result['matched'],
            $result['attached']
        );

        if (($result['removed'] ?? 0) > 0) {
            $message .= sprintf(' Removed %d who no longer match.', $result['removed']);
        }

        return $message;
    }
}
