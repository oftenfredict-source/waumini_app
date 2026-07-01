<?php

return [
    'categories' => [
        'general' => [
            'name' => 'General',
            'icon' => 'fa-info-circle',
            'description' => 'Church profile, timezone, and regional preferences.',
        ],
        'membership' => [
            'name' => 'Membership',
            'icon' => 'fa-users',
            'description' => 'Member registration and ID generation rules.',
        ],
        'finance' => [
            'name' => 'Finance',
            'icon' => 'fa-money',
            'description' => 'Financial workflows and approval preferences.',
        ],
        'notifications' => [
            'name' => 'Notifications',
            'icon' => 'fa-bell',
            'description' => 'SMS and email notification options.',
        ],
        'security' => [
            'name' => 'Security',
            'icon' => 'fa-shield',
            'description' => 'Session and login security preferences.',
        ],
    ],

    'defaults' => [
        'date_format' => 'd/m/Y',
        'locale' => 'en',
        'child_max_age' => 18,
        'auto_generate_member_id' => true,
        'member_id_prefix' => 'WL',
        'require_member_phone' => false,
        'finance_approval_required' => true,
        'fiscal_year_start_month' => 1,
        'sms_enabled' => false,
        'use_custom_sender_id' => false,
        'sms_sender_id' => '',
        'email_notifications' => true,
        'announcement_sms' => false,
        'member_credentials_sms' => false,
        'password_reset_sms' => true,
        'finance_approval_sms' => true,
        'otp_login_enabled' => false,
        'session_timeout_minutes' => 120,
        'max_login_attempts' => 5,
    ],

    'timezones' => [
        'Africa/Dar_es_Salaam' => 'Dar es Salaam (EAT)',
        'Africa/Nairobi' => 'Nairobi (EAT)',
        'Africa/Kampala' => 'Kampala (EAT)',
        'UTC' => 'UTC',
    ],

    'date_formats' => [
        'd/m/Y' => 'DD/MM/YYYY',
        'm/d/Y' => 'MM/DD/YYYY',
        'Y-m-d' => 'YYYY-MM-DD',
        'd-m-Y' => 'DD-MM-YYYY',
    ],
];
