<?php
/**
 * NewsWoo helper functions.
 *
 * @package NewsWoo
 */

use NewsWoo\Models\{Subscription, Membership, User};
use NewsWoo\Services\{SubscriptionService, PaywallService};

if (!function_exists('newswoo_can_access')) {
    /**
     * Check if the current user can access a post.
     */
    function newswoo_can_access(int $postId): bool
    {
        $userId = get_current_user_id();
        return PaywallService::canAccess($userId, $postId);
    }
}

if (!function_exists('newswoo_is_subscriber')) {
    /**
     * Check if the current user has an active subscription.
     */
    function newswoo_is_subscriber(): bool
    {
        $userId = get_current_user_id();
        return SubscriptionService::isActive($userId);
    }
}

if (!function_exists('newswoo_mrr')) {
    /**
     * Get Monthly Recurring Revenue.
     */
    function newswoo_mrr(): float
    {
        return SubscriptionService::mrr();
    }
}

if (!function_exists('newswoo_churn_rate')) {
    /**
     * Get churn rate (last 30 days).
     */
    function newswoo_churn_rate(): float
    {
        return SubscriptionService::churnRate();
    }
}

