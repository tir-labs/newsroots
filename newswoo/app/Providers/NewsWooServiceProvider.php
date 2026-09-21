<?php
/**
 * NewsWoo Service Provider.
 *
 * Registers Eloquent models, services, and the WooCommerce bridge.
 * Boots via Acorn's service provider system.
 *
 * @package NewsWoo
 */

namespace NewsWoo\Providers;

use Illuminate\Support\ServiceProvider;
use NewsWoo\Services\{SubscriptionService, PaywallService, WooCommerceBridge};

class NewsWooServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(SubscriptionService::class);
        $this->app->singleton(PaywallService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        WooCommerceBridge::init();
        $this->registerRestRoutes();
    }

    /**
     * Register NewsWoo REST API endpoints.
     */
    protected function registerRestRoutes(): void
    {
        add_action('rest_api_init', function () {
            register_rest_route('newspack-woo/v1', '/stats/mrr', [
                'methods' => 'GET',
                'callback' => [SubscriptionService::class, 'mrr'],
                'permission_callback' => fn() => current_user_can('manage_options'),
            ]);

            register_rest_route('newspack-woo/v1', '/stats/churn', [
                'methods' => 'GET',
                'callback' => [SubscriptionService::class, 'churnRate'],
                'permission_callback' => fn() => current_user_can('manage_options'),
            ]);

            register_rest_route('newspack-woo/v1', '/stats/subscribers', [
                'methods' => 'GET',
                'callback' => [SubscriptionService::class, 'countByStatus'],
                'permission_callback' => fn() => current_user_can('manage_options'),
            ]);

            register_rest_route('newspack-woo/v1', '/access/(?P<post_id>\\d+)', [
                'methods' => 'GET',
                'callback' => function (\WP_REST_Request $request) {
                    $userId = get_current_user_id();
                    $postId = $request->get_param('post_id');
                    return [
                        'can_access' => PaywallService::canAccess($userId, $postId),
                        'is_gated' => PaywallService::isGated($postId),
                        'tier' => PaywallService::tierLabel($postId),
                    ];
                },
                'permission_callback' => '__return_true',
            ]);

            register_rest_route('newspack-woo/v1', '/user/summary', [
                'methods' => 'GET',
                'callback' => function () {
                    $userId = get_current_user_id();
                    if (!$userId) {
                        return new \WP_Error('not_logged_in', 'Not logged in', ['status' => 401]);
                    }
                    return [
                        'is_subscriber' => SubscriptionService::isActive($userId),
                        'subscriptions' => SubscriptionService::forUser($userId),
                        'access' => PaywallService::userAccessSummary($userId),
                        'ltv' => SubscriptionService::lifetimeValue($userId),
                    ];
                },
                'permission_callback' => '__return_true',
            ]);
        });
    }
}

