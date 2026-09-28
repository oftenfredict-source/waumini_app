<?php

namespace App\Http\Controllers\Api\MemberPortal;

use App\Models\SupportTicket;
use App\Models\SystemSetting;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HelpController extends MemberPortalController
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'category' => ['required', 'string', 'in:login,payments,incorrect_info,app_broken,other,help'],
            'body' => ['required', 'string', 'max:5000'],
            'screenshot' => ['nullable', 'image', 'max:4096'],
        ]);

        $member = $this->member();
        $labels = [
            'login' => 'Login issue',
            'payments' => 'Payment issue',
            'incorrect_info' => 'Incorrect information',
            'app_broken' => 'App not working',
            'other' => 'Other',
            'help' => 'Help request',
        ];

        $body = trim($data['body']);
        $body .= "\n\nMember: {$member->full_name}";
        if ($member->member_number) {
            $body .= " ({$member->member_number})";
        }

        if ($request->hasFile('screenshot')) {
            $path = $request->file('screenshot')->store(
                "churches/{$member->church_id}/help",
                'public'
            );
            $body .= "\nScreenshot: ".asset('storage/'.$path);
        }

        $ticket = SupportTicket::query()->create([
            'church_id' => $member->church_id,
            'created_by_user_id' => $request->user()?->id,
            'category' => $data['category'],
            'priority' => 'medium',
            'status' => 'open',
            'subject' => $labels[$data['category']] ?? 'Member help',
            'body' => $body,
        ]);

        return ApiResponse::success(['id' => $ticket->id], 'Help request submitted.', 201);
    }

    public function contact(): JsonResponse
    {
        $church = $this->member()->church;

        return ApiResponse::success([
            'church_name' => $church?->name,
            'church_phone' => $church?->phone,
            'church_email' => $church?->email,
            'support_email' => SystemSetting::getValue('general', 'support_email', 'support@wauminilink.com'),
            'support_phone' => SystemSetting::getValue('general', 'support_phone', $church?->phone),
        ]);
    }
}
