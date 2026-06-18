<?php

namespace App\Services\Church;

use App\Enums\ChurchStaffRole;
use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Models\Church;
use App\Models\User;
use App\Services\Owner\AuditLogService;
use App\Services\Sms\ChurchSmsService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ChurchStaffUserService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly ChurchSmsService $churchSmsService,
    ) {}

    /**
     * @param  array{name: string, email: string, role: string, phone?: string|null}  $data
     * @return array{user: User, password: string}
     */
    public function create(Church $church, array $data): array
    {
        $role = ChurchStaffRole::from($data['role']);
        $plainPassword = Str::password(12, symbols: false);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => $plainPassword,
            'user_type' => $role->userType(),
            'status' => UserStatus::Active,
            'church_id' => $church->id,
            'branch_id' => $data['branch_id'] ?? null,
            'email_verified_at' => now(),
        ]);

        $user->syncRoles([$role->value]);

        $this->auditLogService->log('church.user.created', $user, null, [
            'email' => $user->email,
            'role' => $role->value,
        ], $church->id);

        return ['user' => $user, 'password' => $plainPassword];
    }

    /**
     * @param  array{name: string, email: string, role: string, phone?: string|null, status: string}  $data
     */
    public function update(Church $church, User $user, array $data): User
    {
        $this->ensureChurchStaff($church, $user);

        $role = ChurchStaffRole::from($data['role']);
        $old = $user->only(['name', 'email', 'phone', 'user_type', 'status']);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'user_type' => $role->userType(),
            'status' => UserStatus::from($data['status']),
            'branch_id' => $data['branch_id'] ?? null,
        ]);

        $user->syncRoles([$role->value]);

        $this->auditLogService->log('church.user.updated', $user, $old, $user->only([
            'name', 'email', 'phone', 'user_type', 'status',
        ]), $church->id);

        return $user->fresh('roles');
    }

    public function resetPassword(Church $church, User $user): string
    {
        $this->ensureChurchStaff($church, $user);

        $plainPassword = Str::password(12, symbols: false);
        $user->update(['password' => $plainPassword]);

        $this->auditLogService->log('church.user.password_reset', $user, null, [
            'email' => $user->email,
        ], $church->id);

        $user->loadMissing('member');
        $sms = $this->churchSmsService->sendPasswordReset($church, $user, $plainPassword);

        if (! ($sms['ok'] ?? false)) {
            Log::warning('Staff password reset SMS not sent', [
                'user_id' => $user->id,
                'reason' => $sms['reason'] ?? 'unknown',
            ]);
        }

        return $plainPassword;
    }

    public function ensureChurchStaff(Church $church, User $user): void
    {
        if ($user->church_id !== $church->id || ! in_array($user->user_type, UserType::churchStaffTypes(), true)) {
            throw ValidationException::withMessages([
                'user' => 'This user does not belong to your church staff accounts.',
            ]);
        }
    }
}
