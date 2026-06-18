<?php

namespace App\Http\Controllers\Church\System;

use App\Http\Requests\Church\SendManualSmsRequest;
use App\Http\Requests\Church\UpdateSmsMessageRequest;
use App\Http\Requests\Church\UpdateSmsTemplateRequest;
use App\Models\SmsLog;
use App\Services\Sms\ChurchSmsService;
use App\Services\Sms\SmsTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmsStoreController extends SystemController
{
    public function __construct(
        private readonly SmsTemplateService $templateService,
        private readonly ChurchSmsService $churchSmsService,
    ) {
        $this->middleware(function ($request, $next) {
            abort_unless($request->user()?->can('system.settings'), 403);

            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        $church = $this->church();
        $tab = $request->string('tab')->toString() ?: 'templates';

        $templates = $this->templateService->listForChurch($church);

        $messagesQuery = SmsLog::query()
            ->where('church_id', $church->id)
            ->latest();

        if ($request->filled('status')) {
            $messagesQuery->where('status', $request->string('status')->toString());
        }

        if ($request->filled('context')) {
            $messagesQuery->where('context', $request->string('context')->toString());
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $messagesQuery->where(function ($query) use ($search) {
                $query->where('recipient', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $messagesQuery->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $messagesQuery->whereDate('created_at', '<=', $request->date_to);
        }

        $messages = $messagesQuery->paginate(20, ['*'], 'messages_page')->withQueryString();

        $baseQuery = SmsLog::query()->where('church_id', $church->id);

        return view('church.system.sms.index', [
            'church' => $church,
            'tab' => $tab,
            'templates' => $templates,
            'messages' => $messages,
            'contexts' => (clone $baseQuery)->select('context')->distinct()->orderBy('context')->pluck('context'),
            'stats' => [
                'total' => (clone $baseQuery)->count(),
                'sent' => (clone $baseQuery)->where('status', 'sent')->count(),
                'failed' => (clone $baseQuery)->where('status', 'failed')->count(),
                'this_month' => SmsLog::monthlyCountForChurch($church->id),
            ],
            'smsEnabled' => $this->churchSmsService->churchSmsEnabled($church),
            'platformSmsEnabled' => $this->churchSmsService->platformSmsEnabled(),
        ]);
    }

    public function editTemplate(string $key): View
    {
        $church = $this->church();
        $templates = $this->templateService->listForChurch($church);
        $item = $templates->firstWhere('key', $key);

        abort_if($item === null, 404);

        return view('church.system.sms.edit-template', [
            'church' => $church,
            'item' => $item,
        ]);
    }

    public function updateTemplate(UpdateSmsTemplateRequest $request, string $key): RedirectResponse
    {
        $church = $this->church();

        $this->templateService->updateTemplate(
            $church,
            $key,
            $request->validated('body'),
            $request->boolean('is_active'),
            $request->user(),
        );

        return redirect()
            ->route('church.system.sms.index', ['tab' => 'templates'])
            ->with('success', 'SMS template updated successfully.');
    }

    public function showMessage(SmsLog $smsLog): View
    {
        $church = $this->church();
        abort_unless($smsLog->church_id === $church->id, 404);

        $smsLog->load('editor');

        return view('church.system.sms.show-message', [
            'church' => $church,
            'smsLog' => $smsLog,
        ]);
    }

    public function updateMessage(UpdateSmsMessageRequest $request, SmsLog $smsLog): RedirectResponse
    {
        $church = $this->church();
        abort_unless($smsLog->church_id === $church->id, 404);

        $smsLog->update([
            'message' => $request->validated('message'),
            'recipient' => $request->validated('recipient'),
            'edited_at' => now(),
            'edited_by' => $request->user()?->id,
        ]);

        return redirect()
            ->route('church.system.sms.messages.show', $smsLog)
            ->with('success', 'SMS message updated in the store.');
    }

    public function resendMessage(SmsLog $smsLog): RedirectResponse
    {
        $church = $this->church();
        abort_unless($smsLog->church_id === $church->id, 404);

        $result = $this->churchSmsService->resendLoggedMessage($church, $smsLog);

        return back()->with(
            ($result['ok'] ?? false) ? 'success' : 'error',
            ($result['ok'] ?? false)
                ? 'SMS resent successfully to '.$smsLog->recipient.'.'
                : 'SMS could not be sent. Check SMS settings and monthly limit.',
        );
    }

    public function sendManual(SendManualSmsRequest $request): RedirectResponse
    {
        $church = $this->church();

        $result = $this->churchSmsService->sendManualMessage(
            $church,
            $request->validated('recipient'),
            $request->validated('message'),
            'manual',
        );

        return redirect()
            ->route('church.system.sms.index', ['tab' => 'messages'])
            ->with(
                ($result['ok'] ?? false) ? 'success' : 'error',
                ($result['ok'] ?? false)
                    ? 'SMS sent successfully.'
                    : 'SMS could not be sent. Check SMS settings and monthly limit.',
            );
    }
}
