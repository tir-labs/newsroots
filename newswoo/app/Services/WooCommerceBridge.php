<?php
/**
 * NewsWoo WooCommerce Bridge.
 *
 * Hooks into WooCommerce's action/filter system to keep Eloquent models
 * in sync with WC data. This is the compatibility layer that lets
 * Newspack's existing WooCommerce integration continue working while
 * NewsWoo's Eloquent models provide the clean API.
 *
 * @package NewsWoo
 */

namespace NewsWoo\Services;

use NewsWoo\Models\{Subscription, Membership, Order, Product};

class WooCommerceBridge
{
    /**
     * Initialize all bridge hooks.
     */
    public static function init(): void
    {
        // Order lifecycle.
        add_action('woocommerce_order_status_completed', [self::class, 'onOrderCompleted'], 10, 1);
        add_action('woocommerce_order_status_processing', [self::class, 'onOrderPaid'], 10, 1);
        add_action('woocommerce_order_status_refunded', [self::class, 'onOrderRefunded'], 10, 1);

        // Subscription lifecycle (WC Subscriptions hooks).
        add_action('activated_subscription', [self::class, 'onSubscriptionActivated'], 10, 1);
        add_action('cancelled_subscription', [self::class, 'onSubscriptionCancelled'], 10, 1);
        add_action('expired_subscription', [self::class, 'onSubscriptionExpired'], 10, 1);
        add_action('suspended_subscription', [self::class, 'onSubscriptionSuspended'], 10, 1);

        // Membership lifecycle (WC Memberships hooks).
        add_action('wc_memberships_user_membership_activated', [self::class, 'onMembershipActivated'], 10, 1);
        add_action('wc_memberships_user_membership_cancelled', [self::class, 'onMembershipCancelled'], 10, 1);
        add_action('wc_memberships_user_membership_ended', [self::class, 'onMembershipEnded'], 10, 1);
    }

    /*
    |--------------------------------------------------------------------------
    | Order Events
    |--------------------------------------------------------------------------
    */

    public static function onOrderCompleted(int $orderId): void
    {
        $order = Order::find($orderId);
        if (!$order) return;

        // Fire data events for Newspack's contact sync.
        do_action('newspack_data_event_order_completed', $order);
    }

    public static function onOrderPaid(int $orderId): void
    {
        $order = Order::find($orderId);
        if (!$order) return;

        do_action('newspack_data_event_order_paid', $order);
    }

    public static function onOrderRefunded(int $orderId): void
    {
        $order = Order::find($orderId);
        if (!$order) return;

        do_action('newspack_data_event_order_refunded', $order);
    }

    /*
    |--------------------------------------------------------------------------
    | Subscription Events
    |--------------------------------------------------------------------------
    */

    public static function onSubscriptionActivated(int $subscriptionId): void
    {
        $sub = Subscription::find($subscriptionId);
        if (!$sub) return;

        // Fire data event for Newspack.
        do_action('newspack_data_event_subscription_activated', $sub);

        // Sync contact status.
        do_action('newspack_data_event_subscription_status_changed', $sub, 'active');
    }

    public static function onSubscriptionCancelled(int $subscriptionId): void
    {
        $sub = Subscription::find($subscriptionId);
        if (!$sub) return;

        do_action('newspack_data_event_subscription_cancelled', $sub);
        do_action('newspack_data_event_subscription_status_changed', $sub, 'cancelled');
    }

    public static function onSubscriptionExpired(int $subscriptionId): void
    {
        $sub = Subscription::find($subscriptionId);
        if (!$sub) return;

        do_action('newspack_data_event_subscription_expired', $sub);
        do_action('newspack_data_event_subscription_status_changed', $sub, 'expired');
    }

    public static function onSubscriptionSuspended(int $subscriptionId): void
    {
        $sub = Subscription::find($subscriptionId);
        if (!$sub) return;

        do_action('newspack_data_event_subscription_suspended', $sub);
        do_action('newspack_data_event_subscription_status_changed', $sub, 'on-hold');
    }

    /*
    |--------------------------------------------------------------------------
    | Membership Events
    |--------------------------------------------------------------------------
    */

    public static function onMembershipActivated(int $membershipId): void
    {
        $membership = Membership::find($membershipId);
        if (!$membership) return;

        do_action('newspack_data_event_membership_activated', $membership);
    }

    public static function onMembershipCancelled(int $membershipId): void
    {
        $membership = Membership::find($membershipId);
        if (!$membership) return;

        do_action('newspack_data_event_membership_cancelled', $membership);
    }

    public static function onMembershipEnded(int $membershipId): void
    {
        $membership = Membership::find($membershipId);
        if (!$membership) return;

        do_action('newspack_data_event_membership_ended', $membership);
    }
}

