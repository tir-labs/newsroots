<?php
/**
 * NewsWoo configuration.
 *
 * @package NewsWoo
 */

return [
    /*
    |--------------------------------------------------------------------------
    | NewsWoo Version
    |--------------------------------------------------------------------------
    */
    'version' => '0.1.0',

    /*
    |--------------------------------------------------------------------------
    | Subscription Statuses
    |--------------------------------------------------------------------------
    | Statuses that Newspack considers "active" for paywall access.
    */
    'active_subscription_statuses' => ['wc-active', 'wc-pending-cancel'],
    'former_subscription_statuses' => ['wc-on-hold', 'wc-cancelled', 'wc-expired'],

    /*
    |--------------------------------------------------------------------------
    | Content Gating
    |--------------------------------------------------------------------------
    */
    'gate' => [
        'enabled' => true,
        'default_message' => 'Subscribe to continue reading.',
        'show_excerpt' => true,
        'excerpt_length' => 40,
    ],

    /*
    |--------------------------------------------------------------------------
    | REST API
    |--------------------------------------------------------------------------
    */
    'api' => [
        'namespace' => 'newspack-woo/v1',
        'require_auth' => true,
    ],
];

