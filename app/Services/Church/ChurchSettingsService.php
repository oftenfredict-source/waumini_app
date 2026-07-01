<?php

namespace App\Services\Church;

use App\Models\Church;
use App\Models\SystemSetting;
use App\Services\Church\MemberIdPrefixService;
use App\Services\Owner\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ChurchSettingsService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function all(Church $church): array
    {
        $stored = is_array($church->settings) ? $church->settings : [];

        return array_merge(config('church_settings.defaults', []), $stored, [
            'church_name' => $church->name,
            'church_email' => $church->email,
            'church_phone' => $church->phone,
            'church_address' => $church->address,
            'church_city' => $church->city,
            'church_country' => $church->country,
            'denomination' => $church->denomination,
            'pastor_name' => $church->pastor_name,
            'church_logo_url' => $church->logoUrl(),
            'timezone' => $church->timezone ?? 'UTC',
            'currency' => $church->currency ?? 'TZS',
            'locale' => $church->locale ?? 'en',
        ]);
    }

    public function get(Church $church, string $key, mixed $default = null): mixed
    {
        return Arr::get($this->all($church), $key, $default);
    }

    public function resolveSenderId(Church $church): string
    {
        if ((bool) $this->get($church, 'use_custom_sender_id', false)) {
            $custom = trim((string) $this->get($church, 'sms_sender_id', ''));

            if ($custom !== '') {
                return $custom;
            }
        }

        return (string) SystemSetting::smsGatewayConfig()['sender_id'];
    }

    /**
     * @return array<string, mixed>
     */
    public function validateTab(string $tab, array $input, ?Request $request = null, ?Church $church = null): array
    {
        $data = match ($tab) {
            'general' => array_merge(
                validator($input, [
                    'church_name' => ['required', 'string', 'max:255'],
                    'church_email' => ['required', 'email', 'max:255'],
                    'church_phone' => ['nullable', 'string', 'max:50'],
                    'church_address' => ['nullable', 'string', 'max:500'],
                    'church_city' => ['nullable', 'string', 'max:100'],
                    'church_country' => ['nullable', 'string', 'max:100'],
                    'denomination' => ['nullable', 'string', 'max:150'],
                    'pastor_name' => ['nullable', 'string', 'max:150'],
                    'timezone' => ['required', 'string', Rule::in(array_keys(config('church_settings.timezones')))],
                    'currency' => ['required', 'string', Rule::in(array_keys(config('currencies')))],
                    'locale' => ['required', 'string', 'max:10'],
                    'date_format' => ['required', 'string', Rule::in(array_keys(config('church_settings.date_formats')))],
                    'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
                ])->validate(),
                [
                    'remove_logo' => filter_var($input['remove_logo'] ?? false, FILTER_VALIDATE_BOOLEAN),
                ],
            ),
            'membership' => array_merge(
                validator($input, [
                    'child_max_age' => ['required', 'integer', 'min:1', 'max:30'],
                    'member_id_prefix' => ['required', 'string', 'min:2', 'max:6', 'regex:/^[A-Za-z0-9]+$/'],
                ])->validate(),
                [
                    'auto_generate_member_id' => filter_var($input['auto_generate_member_id'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'require_member_phone' => filter_var($input['require_member_phone'] ?? false, FILTER_VALIDATE_BOOLEAN),
                ],
            ),
            'finance' => array_merge(
                validator($input, [
                    'fiscal_year_start_month' => ['required', 'integer', 'min:1', 'max:12'],
                ])->validate(),
                [
                    'finance_approval_required' => filter_var($input['finance_approval_required'] ?? false, FILTER_VALIDATE_BOOLEAN),
                ],
            ),
            'notifications' => array_merge(
                validator($input, [
                    'sms_sender_id' => [
                        filter_var($input['use_custom_sender_id'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 'required' : 'nullable',
                        'string',
                        'max:20',
                    ],
                ])->validate(),
                [
                    'sms_enabled' => filter_var($input['sms_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'use_custom_sender_id' => filter_var($input['use_custom_sender_id'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'sms_sender_id' => trim((string) ($input['sms_sender_id'] ?? '')),
                    'email_notifications' => filter_var($input['email_notifications'] ?? true, FILTER_VALIDATE_BOOLEAN),
                    'announcement_sms' => filter_var($input['announcement_sms'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'member_credentials_sms' => filter_var($input['member_credentials_sms'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'password_reset_sms' => filter_var($input['password_reset_sms'] ?? true, FILTER_VALIDATE_BOOLEAN),
                    'finance_approval_sms' => filter_var($input['finance_approval_sms'] ?? true, FILTER_VALIDATE_BOOLEAN),
                ],
            ),
            'security' => array_merge(
                validator($input, [
                    'session_timeout_minutes' => ['required', 'integer', 'min:15', 'max:480'],
                    'max_login_attempts' => ['required', 'integer', 'min:3', 'max:20'],
                ])->validate(),
                [
                    'otp_login_enabled' => filter_var($input['otp_login_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
                ],
            ),
            default => abort(404),
        };

        if ($tab === 'membership') {
            $data['member_id_prefix'] = $church
                ? app(MemberIdPrefixService::class)->assertAvailable($data['member_id_prefix'], $church->id)
                : strtoupper($data['member_id_prefix']);
        }

        return $data;
    }

    public function updateTab(Church $church, string $tab, array $data, ?Request $request = null): Church
    {
        $oldSettings = $this->all($church);

        if ($tab === 'general') {
            if (! empty($data['remove_logo'])) {
                $this->deleteLogo($church);
            }

            if ($request?->hasFile('logo')) {
                $this->uploadLogo($church, $request->file('logo'));
            }

            $church->update([
                'name' => $data['church_name'],
                'email' => $data['church_email'],
                'phone' => $data['church_phone'] ?? null,
                'address' => $data['church_address'] ?? null,
                'city' => $data['church_city'] ?? null,
                'country' => $data['church_country'] ?? null,
                'denomination' => $data['denomination'] ?? null,
                'pastor_name' => $data['pastor_name'] ?? null,
                'timezone' => $data['timezone'],
                'currency' => strtoupper($data['currency']),
                'locale' => $data['locale'],
            ]);

            $admin = $church->adminUser;
            if ($admin && empty($admin->phone) && ! empty($church->phone)) {
                $admin->update(['phone' => $church->phone]);
            }

            $settings = array_merge($church->settings ?? [], [
                'date_format' => $data['date_format'],
            ]);
        } else {
            $settings = array_merge($church->settings ?? [], $data);
        }

        $church->update(['settings' => $settings]);
        $church->refresh();

        $this->auditLogService->log(
            'church.settings.updated',
            $church,
            ['tab' => $tab, 'before' => $oldSettings],
            ['tab' => $tab, 'after' => $this->all($church)],
            $church->id,
        );

        return $church;
    }

    private function uploadLogo(Church $church, UploadedFile $logo): void
    {
        $this->deleteLogo($church);

        $church->update([
            'logo_path' => $logo->store("churches/{$church->id}/branding", 'public'),
        ]);
    }

    private function deleteLogo(Church $church): void
    {
        if ($church->logo_path) {
            Storage::disk('public')->delete($church->logo_path);
            $church->update(['logo_path' => null]);
        }
    }
}
