<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('members:process-aged-out-children')->daily();
Schedule::command('attendance:notify-missed-sundays')->dailyAt('08:00');
Schedule::command('backup:keep-google-token')->weeklyOn(1, '03:15');

try {
    $backupEnabled = (bool) \App\Models\SystemSetting::getValue('backup', 'enabled', false);
    $backupAt = (string) \App\Models\SystemSetting::getValue('backup', 'run_at', '02:00');
} catch (\Throwable) {
    $backupEnabled = false;
    $backupAt = '02:00';
}

if ($backupEnabled) {
    Schedule::command('backup:database')->dailyAt(
        preg_match('/^\d{2}:\d{2}$/', $backupAt) ? $backupAt : '02:00'
    );
}
