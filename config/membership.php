<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Child independence age
    |--------------------------------------------------------------------------
    |
    | When a dependant child reaches this age, they can be converted to an
    | independent permanent member.
    |
    */
    'child_independence_age' => (int) env('CHILD_INDEPENDENCE_AGE', 21),

    /*
    |--------------------------------------------------------------------------
    | Sunday School age range
    |--------------------------------------------------------------------------
    |
    | Children in this age range attend Sunday School (not the main service).
    |
    */
    'sunday_school_min_age' => (int) env('SUNDAY_SCHOOL_MIN_AGE', 3),
    'sunday_school_max_age' => (int) env('SUNDAY_SCHOOL_MAX_AGE', 12),

    /*
    |--------------------------------------------------------------------------
    | Main service child age range
    |--------------------------------------------------------------------------
    |
    | Teenagers in this range attend the main church service with adults.
    |
    */
    'main_service_child_min_age' => (int) env('MAIN_SERVICE_CHILD_MIN_AGE', 13),

];
