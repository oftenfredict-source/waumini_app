<?php

namespace App\Services\Church;

use App\Enums\DepartmentStatus;
use App\Enums\LeadershipPosition;
use App\Models\Church;
use App\Models\Department;
use App\Models\SystemSetting;
use App\Services\Church\MemberIdPrefixService;
use App\Services\Owner\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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

    /**
     * Age at which a dependant leaves the Children list and can convert to a member (youth).
     * Children younger than this age remain active in Children.
     */
    public function childGraduationAge(Church $church): int
    {
        $age = (int) $this->get(
            $church,
            'child_max_age',
            config('membership.child_independence_age', 13),
        );

        return max(1, min(30, $age));
    }

    public function youthMinAge(Church $church): int
    {
        $age = (int) $this->get($church, 'youth_min_age', $this->childGraduationAge($church));

        return max(0, min(120, $age));
    }

    public function youthMaxAge(Church $church): int
    {
        $age = (int) $this->get($church, 'youth_max_age', 21);

        return max($this->youthMinAge($church), min(120, $age));
    }

    /**
     * Kipaimara fields are hidden for people younger than this age.
     */
    public function kipaimaraMinAge(Church $church): int
    {
        $age = (int) $this->get($church, 'kipaimara_min_age', 11);

        return max(0, min(30, $age));
    }

    public function canShowKipaimara(?Church $church, ?int $age, bool $alreadyMarked = false): bool
    {
        if (! $church) {
            return false;
        }

        if (! (bool) $this->get($church, 'kipaimara_registration_enabled', false) && ! $alreadyMarked) {
            return false;
        }

        if ($age === null) {
            return (bool) $this->get($church, 'kipaimara_registration_enabled', false) || $alreadyMarked;
        }

        return $age >= $this->kipaimaraMinAge($church);
    }

    /**
     * Envelope is optional through the youth max age (inclusive), required from the next year.
     * Example: youth 13–21 → optional; age 22+ → required.
     */
    public function envelopeRequiredForAge(Church $church, ?int $age): bool
    {
        if ($age === null) {
            return true;
        }

        return $age > $this->youthMaxAge($church);
    }

    public function envelopeRequiredFromAge(Church $church): int
    {
        return $this->youthMaxAge($church) + 1;
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
            'membership' => $this->normalizeMembershipTab(
                array_merge(
                    validator($input, [
                        'child_max_age' => ['required', 'integer', 'min:1', 'max:30'],
                        'youth_min_age' => ['required', 'integer', 'min:1', 'max:120'],
                        'youth_max_age' => ['required', 'integer', 'min:1', 'max:120'],
                        'kipaimara_min_age' => ['required', 'integer', 'min:0', 'max:30'],
                        'member_id_prefix' => ['required', 'string', 'min:2', 'max:6', 'regex:/^[A-Za-z0-9]+$/'],
                        'department_assignment_rules' => ['nullable', 'array'],
                        'department_assignment_rules.*.department_id' => ['nullable'],
                        'department_assignment_rules.*.min_age' => ['nullable', 'integer', 'min:0', 'max:120'],
                        'department_assignment_rules.*.max_age' => ['nullable', 'integer', 'min:0', 'max:120'],
                        'department_assignment_rules.*.genders' => ['nullable', 'array'],
                        'department_assignment_rules.*.genders.*' => ['string', Rule::in(['male', 'female'])],
                        'department_assignment_rules.*.leadership_positions' => ['nullable', 'array'],
                        'department_assignment_rules.*.leadership_positions.*' => ['string', Rule::enum(LeadershipPosition::class)],
                    ])->validate(),
                    [
                        'auto_generate_member_id' => filter_var($input['auto_generate_member_id'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'require_member_phone' => filter_var($input['require_member_phone'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'kipaimara_registration_enabled' => filter_var($input['kipaimara_registration_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'children_education_details_enabled' => filter_var($input['children_education_details_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'department_assignment_enabled' => filter_var($input['department_assignment_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    ],
                )
            ),
            'finance' => array_merge(
                validator($input, [
                    'fiscal_year_start_month' => ['required', 'integer', 'min:1', 'max:12'],
                    'custom_offering_types' => ['nullable', 'array', 'max:30'],
                    'custom_offering_types.*' => ['nullable', 'string', 'max:100'],
                ])->validate(),
                [
                    'finance_approval_required' => filter_var($input['finance_approval_required'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'custom_offering_types' => $this->normalizeCustomOfferingTypes($input['custom_offering_types'] ?? []),
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
                    'missed_attendance_sms' => filter_var($input['missed_attendance_sms'] ?? true, FILTER_VALIDATE_BOOLEAN),
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

            $data = $this->normalizeDepartmentAssignmentRules($data, $church);
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

        if ($tab === 'membership') {
            unset(
                $settings['age_department_routing_enabled'],
                $settings['children_department_id'],
                $settings['youth_department_id'],
                $settings['children_department_max_age'],
                $settings['youth_department_min_age'],
                $settings['youth_department_max_age'],
            );
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

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeDepartmentAssignmentRules(array $data, ?Church $church): array
    {
        $enabled = (bool) ($data['department_assignment_enabled'] ?? false);
        $rawRules = is_array($data['department_assignment_rules'] ?? null)
            ? $data['department_assignment_rules']
            : [];

        $rules = [];
        $errors = [];

        foreach (array_values($rawRules) as $index => $rawRule) {
            if (! is_array($rawRule)) {
                continue;
            }

            $departmentId = $rawRule['department_id'] ?? null;
            $departmentId = $departmentId !== null && $departmentId !== ''
                ? (int) $departmentId
                : null;

            $minAge = array_key_exists('min_age', $rawRule) && $rawRule['min_age'] !== null && $rawRule['min_age'] !== ''
                ? (int) $rawRule['min_age']
                : null;
            $maxAge = array_key_exists('max_age', $rawRule) && $rawRule['max_age'] !== null && $rawRule['max_age'] !== ''
                ? (int) $rawRule['max_age']
                : null;

            $positions = array_values(array_unique(array_filter(
                Arr::wrap($rawRule['leadership_positions'] ?? []),
                fn ($value) => is_string($value) && $value !== ''
            )));

            $genders = array_values(array_unique(array_filter(
                Arr::wrap($rawRule['genders'] ?? []),
                fn ($value) => in_array($value, ['male', 'female'], true)
            )));

            $isEmptyRow = ! $departmentId
                && $minAge === null
                && $maxAge === null
                && $positions === []
                && $genders === [];

            if ($isEmptyRow) {
                continue;
            }

            $rowErrors = [];

            if (! $departmentId) {
                $rowErrors["department_assignment_rules.{$index}.department_id"] = 'Select a department for this rule.';
            } elseif ($church && ! $this->departmentBelongsToChurch($church, $departmentId)) {
                $rowErrors["department_assignment_rules.{$index}.department_id"] = 'The selected department is invalid for this church.';
            }

            if ($minAge !== null && $maxAge !== null && $minAge > $maxAge) {
                $rowErrors["department_assignment_rules.{$index}.min_age"] = 'Minimum age cannot be greater than maximum age.';
            }

            if (($minAge !== null && $maxAge === null) || ($minAge === null && $maxAge !== null)) {
                $rowErrors["department_assignment_rules.{$index}.max_age"] = 'Enter both minimum and maximum age for an age rule.';
            }

            if ($minAge === null && $maxAge === null && $positions === [] && $genders === []) {
                $rowErrors["department_assignment_rules.{$index}.genders"] = 'Set gender, an age range, and/or at least one leadership position.';
            }

            foreach ($positions as $position) {
                if (! LeadershipPosition::tryFrom($position)) {
                    $rowErrors["department_assignment_rules.{$index}.leadership_positions"] = 'One or more leadership positions are invalid.';
                    break;
                }
            }

            if ($rowErrors !== []) {
                if ($enabled) {
                    $errors = array_merge($errors, $rowErrors);
                }

                continue;
            }

            $rules[] = [
                'department_id' => $departmentId,
                'genders' => $genders,
                'min_age' => $minAge,
                'max_age' => $maxAge,
                'leadership_positions' => $positions,
            ];
        }

        if ($enabled && $rules === []) {
            $errors['department_assignment_rules'] = 'Add at least one department assignment rule, or turn the feature off.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        // Drop legacy fixed children/youth keys if present.
        unset(
            $data['age_department_routing_enabled'],
            $data['children_department_id'],
            $data['youth_department_id'],
            $data['children_department_max_age'],
            $data['youth_department_min_age'],
            $data['youth_department_max_age'],
        );

        return array_merge($data, [
            'department_assignment_enabled' => $enabled,
            'department_assignment_rules' => $rules,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeMembershipTab(array $data): array
    {
        $childMaxAge = (int) ($data['child_max_age'] ?? 13);
        $youthMinAge = (int) ($data['youth_min_age'] ?? $childMaxAge);
        $youthMaxAge = (int) ($data['youth_max_age'] ?? 21);
        $kipaimaraMinAge = (int) ($data['kipaimara_min_age'] ?? 11);

        $errors = [];

        if ($youthMinAge < $childMaxAge) {
            $errors['youth_min_age'] = 'Youth minimum age cannot be lower than the child graduation age.';
        }

        if ($youthMaxAge < $youthMinAge) {
            $errors['youth_max_age'] = 'Youth maximum age must be greater than or equal to the youth minimum age.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return array_merge($data, [
            'child_max_age' => $childMaxAge,
            'youth_min_age' => $youthMinAge,
            'youth_max_age' => $youthMaxAge,
            'kipaimara_min_age' => $kipaimaraMinAge,
        ]);
    }

    private function departmentBelongsToChurch(Church $church, int $departmentId): bool
    {
        return Department::forChurch($church->id)
            ->whereKey($departmentId)
            ->where('status', DepartmentStatus::Active)
            ->exists();
    }

    /**
     * @param  mixed  $raw
     * @return list<string>
     */
    private function normalizeCustomOfferingTypes(mixed $raw): array
    {
        $items = is_array($raw) ? $raw : [];
        $normalized = [];

        foreach ($items as $item) {
            $label = trim((string) $item);
            if ($label === '') {
                continue;
            }

            $key = mb_strtolower($label);
            if (isset($normalized[$key])) {
                continue;
            }

            $normalized[$key] = $label;
        }

        return array_values($normalized);
    }

    /**
     * @return list<string>
     */
    public function customOfferingTypes(Church $church): array
    {
        $types = $this->get($church, 'custom_offering_types', []);

        return is_array($types) ? array_values(array_filter(array_map('strval', $types))) : [];
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
