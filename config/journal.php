<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Activity Database Table
    |--------------------------------------------------------------------------
    |
    | This value is the table that should be used to store the activities.
    */

    'table' => 'activity_logs',

    /*
    |--------------------------------------------------------------------------
    | Activity Resolvers
    |--------------------------------------------------------------------------
    |
    | These values determine which class should be used to resolve certain
    | attributes of the logged activities.
    */

    'resolvers' => [
        'user' => Scrn\Journal\Resolvers\UserResolver::class,
    ],
];
