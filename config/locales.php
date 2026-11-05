<?php

return [
    'supported' => [
        'en' => [
            'name' => 'English',
            'native' => 'English',
            'flag' => 'gb',
        ],
        'sw' => [
            'name' => 'Swahili',
            'native' => 'Kiswahili',
            'flag' => 'tz',
        ],
    ],

    'default' => env('APP_LOCALE', 'en'),
];
