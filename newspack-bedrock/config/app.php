<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, detailed error pages are shown by Acorn's exception
    | handler. Disable in production.
    |
    */

    'debug' => defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY,

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    */

    'name' => defined('WP_HOME') ? parse_url(WP_HOME, PHP_URL_HOST) : 'Newspack Bedrock',

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    */

    'providers' => [
        // Add custom service providers here.
    ],

    /*
    |--------------------------------------------------------------------------
    | Eloquent Database Connection
    |--------------------------------------------------------------------------
    |
    | Acorn uses the WordPress DB credentials. No separate config needed.
    | Eloquent models auto-resolve via wpdb->dbhost.
    |
    */

    'database' => [
        'default' => 'wordpress',
        'connections' => [
            'wordpress' => [
                'driver' => 'mysql',
                'host' => defined('DB_HOST') ? DB_HOST : 'localhost',
                'database' => defined('DB_NAME') ? DB_NAME : '',
                'username' => defined('DB_USER') ? DB_USER : '',
                'password' => defined('DB_PASSWORD') ? DB_PASSWORD : '',
                'prefix' => defined('DB_PREFIX') ? DB_PREFIX : 'wp_',
            ],
        ],
    ],

];
