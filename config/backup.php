<?php

return [

    'enabled' => (bool) env('BACKUP_ENABLED', false),

    'folder_id' => env('GOOGLE_DRIVE_FOLDER_ID') ?: '',

    'keep_count' => (int) (env('BACKUP_KEEP_COUNT') ?: 14),

    'run_at' => env('BACKUP_RUN_AT') ?: '02:00',

    'mysqldump_path' => env('BACKUP_MYSQLDUMP_PATH') ?: '',

    'google_client_id' => env('GOOGLE_CLIENT_ID') ?: '',

    'google_client_secret' => env('GOOGLE_CLIENT_SECRET') ?: '',

    'google_refresh_token' => env('GOOGLE_DRIVE_REFRESH_TOKEN') ?: '',

    'local_path' => storage_path('app/backups'),

];
