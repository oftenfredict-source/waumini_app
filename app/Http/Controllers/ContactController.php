<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'subject' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        $to = trim((string) SystemSetting::getValue('general', 'support_email', config('mail.from.address')));

        if ($to !== '') {
            $body = implode("\n", [
                'New contact message from the landing page.',
                '',
                'Name: '.$data['name'],
                'Email: '.$data['email'],
                'Phone: '.($data['phone'] ?: '—'),
                'Subject: '.$data['subject'],
                '',
                $data['message'],
            ]);

            try {
                Mail::raw($body, function ($message) use ($to, $data): void {
                    $message->to($to)
                        ->replyTo($data['email'], $data['name'])
                        ->subject('Contact: '.$data['subject']);
                });
            } catch (\Throwable) {
                // Still acknowledge the visitor; mail may be misconfigured locally.
            }
        }

        return redirect()
            ->route('landing')
            ->withFragment('contact')
            ->with('contact_success', __('landing.contact_success'));
    }
}
