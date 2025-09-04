<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Data Source
    |--------------------------------------------------------------------------
    |
    | This option controls the default data source that will be used by the
    | framework. You may set this to any of the data sources defined in
    | the "sources" array below. Use "eloquent" or "query".
    |
    */

    'default' => env('DATASOURCE', 'eloquent'),

    /*
    |--------------------------------------------------------------------------
    | Data Sources
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many data sources as you wish, and you may
    | even define multiple sources of the same type. Sources are used
    | when you need to work with different kinds of data sources.
    |
    | Supported: "eloquent", "query"
    |
    */

    'sources' => [

        'eloquent' => [
            'driver' => 'eloquent',
            // 'model_namespace' => 'App\\Models\\',
        ],

        'query' => [
            'driver' => 'query',
            // 'table_prefix' => '',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-Resolve Models
    |--------------------------------------------------------------------------
    | This option controls whether the repository should attempt to
    | automatically resolve the Eloquent model class based on the
    | repository class name. If set to false, you must define the
    | model class explicitly in the repository.
    |
    */
    'auto_resolve_models' => env('DATASOURCE_AUTO_RESOLVE', true),

    /*
    |--------------------------------------------------------------------------
    | Cache Results
    |--------------------------------------------------------------------------
    |
    | This option controls whether the results from the data source should be
    | cached. If set to true, the results will be cached for the duration
    | of the request.
    |
    */
    'cache_results' => env('DATASOURCE_CACHE', false),
    'cache_duration' => env('DATASOURCE_CACHE_DURATION', 3600), // in seconds

    /*
    |--------------------------------------------------------------------------
    | Default Pagination Size
    |--------------------------------------------------------------------------
    |
    | This option controls the default pagination size for the data source.
    | You may set this to any integer value.
    |
    */

    'default_per_page' => 15,

];