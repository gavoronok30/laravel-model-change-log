<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Table name
    |--------------------------------------------------------------------------
    | Table name for model on logging module
    */
    'table' => 'model_change_log',

    /*
    |--------------------------------------------------------------------------
    | Status module
    |--------------------------------------------------------------------------
    | Enabled or Disabled logging module
    */
    'enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | List of models for logging module
    |--------------------------------------------------------------------------
    | The specified models will be processed by the logging module
    | [
    |   \App\Models\User::class,
    |   \App\Models\Profile::class,
    |   ...
    | ]
    */
    'models' => [
        \App\Models\User::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Exclude fields
    |--------------------------------------------------------------------------
    | List of fields that will not be logged
    | [
    |    'created_at',
    |    'updated_at',
    |    ...
    | ]
    */
    'exclude_fields' => [
        'updated_at',
    ],

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    | Model events to which the logging module will respond
    */
    'events' => [
        /** Create */
        \ItDevgroup\ModelChangeLog\Models\ModelChangeLog::TYPE_EVENT_CREATE => true,
        /** Update */
        \ItDevgroup\ModelChangeLog\Models\ModelChangeLog::TYPE_EVENT_UPDATE => true,
        /** Delete */
        \ItDevgroup\ModelChangeLog\Models\ModelChangeLog::TYPE_EVENT_DELETE => true,
        /** Soft Delete */
        \ItDevgroup\ModelChangeLog\Models\ModelChangeLog::TYPE_EVENT_SOFT_DELETE => true,
        /** Force Delete */
        \ItDevgroup\ModelChangeLog\Models\ModelChangeLog::TYPE_EVENT_FORCE_DELETE => true,
    ],

    'scheduler' => [
        /*
        |--------------------------------------------------------------------------
        | Command for clear date
        |--------------------------------------------------------------------------
        | All records older than the specified number of days will be purged when the purge command is run
        */
        'clear_after_days' => 90
    ]
];
