<?php
/**
 * NewsWoo Paywall Service.
 *
 * Determines whether a reader can access gated content.
 * Integrates with Newspack's Content_Gate and Metering systems.
 *
 * @package NewsWoo
 */

namespace NewsWoo\Services;

use NewsWoo\Models\{User, Membership, MembershipPlan};

class PaywallService
{
    /**
     * Check if a user can access a specific post.
     */
    public static function canAccess(int $userId, int $postId): bool
    {
        if (!$userId) {
            return false;
        }

        // Check all active memberships for this user.
        $memberships = Membership::forUser($userId)->active()->with('plan')->get();

        foreach ($memberships as $membership) {
            if ($membership->canAccess($postId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a post is gated at all.
     */
    public static function isGated(int $postId): bool
    {
        // Check if any membership plan has a rule for this post.
        $plans = MembershipPlan::all();

        foreach ($plans as $plan) {
            if ($plan->grantsAccessTo($postId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the plans that gate a specific post.
     */
    public static function plansForPost(int $postId): array
    {
        $matching = [];
        $plans = MembershipPlan::all();

        foreach ($plans as $plan) {
            if ($plan->grantsAccessTo($postId)) {
                $matching[] = $plan;
            }
        }

        return $matching;
    }

    /**
     * Get the paywall tier label for a post (for display in gate UI).
     */
    public static function tierLabel(int $postId): string
    {
        $plans = self::plansForPost($postId);

        if (empty($plans)) {
            return 'Subscriber';
        }

        return $plans[0]->post_title ?? 'Subscriber';
    }

    /**
     * Get restricted content summary for a user.
     */
    public static function userAccessSummary(int $userId): array
    {
        $memberships = Membership::forUser($userId)->with('plan')->get();

        $active = [];
        $expired = [];

        foreach ($memberships as $m) {
            $entry = [
                'plan' => $m->plan?->post_title ?? 'Unknown',
                'start' => $m->start_date,
                'end' => $m->end_date,
            ];

            if ($m->isActive()) {
                $active[] = $entry;
            } else {
                $expired[] = $entry;
            }
        }

        return ['active' => $active, 'expired' => $expired];
    }
}

