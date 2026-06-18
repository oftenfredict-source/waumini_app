<?php

namespace App\Services\Church;

use App\Models\AuditLog;
use App\Models\Church;
use App\Models\Member;
use App\Models\User;
use App\Enums\UserType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SystemMonitorService
{
    /**
     * @return array<string, mixed>
     */
    public function build(Church $church): array
    {
        $churchUserIds = User::query()
            ->where('church_id', $church->id)
            ->pluck('id');

        $activeSessions = DB::table('sessions')
            ->whereIn('user_id', $churchUserIds)
            ->where('last_activity', '>', now()->subHours(24)->timestamp)
            ->count();

        $recentLogs = AuditLog::query()
            ->where('church_id', $church->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $staffCount = User::query()
            ->where('church_id', $church->id)
            ->whereIn('user_type', array_map(fn (UserType $type) => $type->value, UserType::churchStaffTypes()))
            ->count();

        return [
            'staff_users' => $staffCount,
            'portal_users' => $churchUserIds->count(),
            'members' => Member::forChurch($church->id)->count(),
            'active_sessions' => $activeSessions,
            'audit_logs_7d' => $recentLogs,
            'audit_logs_total' => AuditLog::where('church_id', $church->id)->count(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database' => $this->databaseStatus(),
            'cache' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'timezone' => config('app.timezone'),
            'recent_actions' => $this->recentActions($church->id),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function databaseStatus(): array
    {
        try {
            DB::connection()->getPdo();

            return ['status' => 'connected', 'driver' => config('database.default')];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'driver' => config('database.default')];
        }
    }

    /**
     * @return Collection<int, object>
     */
    private function recentActions(int $churchId): Collection
    {
        return AuditLog::query()
            ->where('church_id', $churchId)
            ->select('action', DB::raw('COUNT(*) as total'))
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('action')
            ->orderByDesc('total')
            ->limit(8)
            ->get();
    }
}
