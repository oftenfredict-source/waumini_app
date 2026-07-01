<?php

return [
    'supported' => [
        'en' => [
            'name' => 'English',
            'native' => 'English',
        ],
        'sw' => [
            'name' => 'Swahili',
            'native' => 'Kiswahili',
        ],
    ],

    'default' => env('APP_LOCALE', 'en'),
];
