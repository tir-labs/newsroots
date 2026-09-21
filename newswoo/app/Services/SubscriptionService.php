<?php
/**
 * NewsWoo Subscription Service.
 *
 * Clean business logic layer using Eloquent models.
 * Newspack hooks into this instead of raw WC_Subscriptions calls.
 *
 * @package NewsWoo
 */

namespace NewsWoo\Services;

use NewsWoo\Models\{Subscription, Order, User};

class SubscriptionService
{
    /**
     * Get all active subscriptions for a user.
     */
    public static function forUser(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return Subscription::forUser($userId)->active()->get();
    }

    /**
     * Check if a user has an active subscription.
     */
    public static function isActive(int $userId): bool
    {
        return Subscription::forUser($userId)->active()->exists();
    }

    /**
     * Check if a user was ever a subscriber (active or former).
     */
    public static function isSubscriber(int $userId): bool
    {
        return Subscription::forUser($userId)
            ->whereIn('post_status', array_merge(Subscription::ACTIVE_STATUSES, Subscription::FORMER_STATUSES))
            ->exists();
    }

    /**
     * Get subscription with all its orders.
     */
    public static function withOrders(int $subscriptionId): ?Subscription
    {
        return Subscription::with(['orders', 'latestOrder', 'customer'])->find($subscriptionId);
    }

    /**
     * Get MRR (Monthly Recurring Revenue).
     */
    public static function mrr(): float
    {
        $subscriptions = Subscription::active()->get();
        $mrr = 0;

        foreach ($subscriptions as $sub) {
            $price = (float) $sub->renewal_price;
            $interval = $sub->billing_interval;
            $period = $sub->billing_period;

            $monthly = match($period) {
                'day' => $price * 30 / max($interval, 1),
                'week' => $price * 4.33 / max($interval, 1),
                'month' => $price / max($interval, 1),
                'year' => $price / (12 * max($interval, 1)),
                default => $price,
            };

            $mrr += $monthly;
        }

        return round($mrr, 2);
    }

    /**
     * Get churn rate (cancelled / total in last 30 days).
     */
    public static function churnRate(): float
    {
        $since = date('Y-m-d H:i:s', strtotime('-30 days'));

        $total = Subscription::where('post_modified', '>=', $since)->count();
        $cancelled = Subscription::cancelled()
            ->where('post_modified', '>=', $since)
            ->count();

        return $total > 0 ? round($cancelled / $total * 100, 1) : 0;
    }

    /**
     * Get subscriber count by status.
     */
    public static function countByStatus(): array
    {
        return Subscription::selectRaw('post_status, count(*) as count')
            ->groupBy('post_status')
            ->pluck('count', 'post_status')
            ->toArray();
    }

    /**
     * Get lifetime value for a user.
     */
    public static function lifetimeValue(int $userId): float
    {
        return (float) Order::forUser($userId)->paid()->sum('meta._order_total');
    }
}

