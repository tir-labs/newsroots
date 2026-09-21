<?php
/**
 * NewsWoo MembershipPlan — Eloquent model.
 *
 * Maps to wp_posts (post_type: 'wc_membership_plan').
 * Defines content restriction rules and what posts/categories a plan grants access to.
 *
 * @package NewsWoo
 */

namespace NewsWoo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{HasMany, MorphToMany};

class MembershipPlan extends Model
{
    protected $table = 'posts';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * All user memberships under this plan.
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class, 'post_parent');
    }

    /**
     * Active user memberships.
     */
    public function activeMemberships(): HasMany
    {
        return $this->memberships()->active();
    }

    /*
    |--------------------------------------------------------------------------
    | Content Restriction Rules
    |--------------------------------------------------------------------------
    */

    /**
     * Get all restriction rules for this plan.
     * Stored as post meta: _wc_memberships_rule_{type}_{id}
     */
    public function getRules(): array
    {
        global $wpdb;

        $rules = $wpdb->get_results($wpdb->prepare(
            "SELECT meta_key, meta_value FROM {$wpdb->postmeta}
             WHERE post_id = %d AND meta_key LIKE '_wc_memberships_rule_%'",
            $this->ID
        ), ARRAY_A);

        $parsed = [];
        foreach ($rules as $rule) {
            $parts = explode('_', str_replace('_wc_memberships_rule_', '', $rule['meta_key']));
            $type = $parts[0] ?? 'post';
            $id = $parts[1] ?? 0;
            $parsed[] = [
                'type' => $type,
                'id' => (int) $id,
                'access' => $rule['meta_value'],
            ];
        }

        return $parsed;
    }

    /**
     * Check if this plan grants access to a specific post.
     */
    public function grantsAccessTo(int $postId): bool
    {
        $rules = $this->getRules();

        foreach ($rules as $rule) {
            if ($rule['type'] === 'post' && $rule['id'] === $postId) {
                return true;
            }

            if ($rule['type'] === 'category') {
                if (has_category($rule['id'], $postId)) {
                    return true;
                }
            }

            if ($rule['type'] === 'taxonomy') {
                $terms = wp_get_post_terms($postId, $rule['taxonomy'] ?? '');
                foreach ($terms as $term) {
                    if ($term->term_id == $rule['id']) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Get posts restricted by this plan.
     */
    public function getRestrictedPostIds(): array
    {
        $rules = $this->getRules();
        $postIds = [];

        foreach ($rules as $rule) {
            if ($rule['type'] === 'post') {
                $postIds[] = $rule['id'];
            }
            if ($rule['type'] === 'category') {
                $posts = get_posts([
                    'category' => $rule['id'],
                    'fields' => 'ids',
                    'posts_per_page' => -1,
                ]);
                $postIds = array_merge($postIds, $posts);
            }
        }

        return array_unique($postIds);
    }
}

