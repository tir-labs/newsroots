<?php
/**
 * Smoke test — verifies models and services are loadable.
 *
 * @package NewsWoo
 */

namespace NewsWoo\Tests;

use NewsWoo\Models\{Subscription, Membership, MembershipPlan, Order, Product, User};
use NewsWoo\Services\{SubscriptionService, PaywallService, WooCommerceBridge};

class SmokeTest extends NewsWooTestCase
{
    public function test_models_exist(): void
    {
        $this->assertTrue(class_exists(Subscription::class));
        $this->assertTrue(class_exists(Membership::class));
        $this->assertTrue(class_exists(MembershipPlan::class));
        $this->assertTrue(class_exists(Order::class));
        $this->assertTrue(class_exists(Product::class));
        $this->assertTrue(class_exists(User::class));
    }

    public function test_services_exist(): void
    {
        $this->assertTrue(class_exists(SubscriptionService::class));
        $this->assertTrue(class_exists(PaywallService::class));
        $this->assertTrue(class_exists(WooCommerceBridge::class));
    }

    public function test_model_properties_match_acorn_docs(): void
    {
        // Per https://roots.io/acorn/docs/eloquent-models/
        // WordPress models must have: table, primaryKey='ID', timestamps=false
        
        $sub = new Subscription();
        $this->assertEquals('posts', $sub->getTable());
        $this->assertEquals('ID', $sub->getKeyName());
        $this->assertFalse($sub->usesTimestamps());

        $user = new User();
        $this->assertEquals('users', $user->getTable());
        $this->assertEquals('ID', $user->getKeyName());
        $this->assertFalse($user->usesTimestamps());

        $order = new Order();
        $this->assertEquals('posts', $order->getTable());
        $this->assertEquals('ID', $order->getKeyName());
        $this->assertFalse($order->usesTimestamps());
    }

    public function test_subscription_statuses(): void
    {
        $this->assertEquals('wc-active', Subscription::STATUS_ACTIVE);
        $this->assertEquals('wc-cancelled', Subscription::STATUS_CANCELLED);
        $this->assertEquals('wc-expired', Subscription::STATUS_EXPIRED);
        $this->assertContains(Subscription::STATUS_ACTIVE, Subscription::ACTIVE_STATUSES);
    }
}

