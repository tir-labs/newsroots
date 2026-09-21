<?php
/**
 * NewsWoo Membership — Eloquent model.
 *
 * Maps to wp_posts (post_type: 'wc_user_membership') with meta.
 * Replaces WC_Memberships_User_Membership + data store.
 *
 * @package NewsWoo
 */

namespace NewsWoo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany};

class Membership extends Model
{
    protected $table = 'posts';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    const STATUS_ACTIVE    = 'wcm-active';
    const STATUS_CANCELLED = 'wcm-cancelled';
    const STATUS_EXPIRED   = 'wcm-expired';
    const STATUS_PAUSED    = 'wcm-paused';
    const STATUS_PENDING   = 'wcm-pending';
    const STATUS_ACTIVE_DELAYED = 'wcm-active-delayed';

    const ACTIVE_STATUSES = [self::STATUS_ACTIVE, self::STATUS_ACTIVE_DELAYED];

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('post_status', self::STATUS_ACTIVE);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('post_author', $userId);
    }

    public function scopeForPlan($query, int $planId)
    {
        return $query->where('post_parent', $planId);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'post_author');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class, 'post_parent');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'post_parent');
    }

    /*
    |--------------------------------------------------------------------------
    | Meta Accessors
    |--------------------------------------------------------------------------
    */

    public function meta(string $key, mixed $default = null): mixed
    {
        $value = get_post_meta($this->ID, $key, true);
        return $value === '' ? $default : $value;
    }

    public function setMeta(string $key, mixed $value): void
    {
        update_post_meta($this->ID, $key, $value);
    }

    public function getStartDateAttribute(): ?string
    {
        return $this->meta('_start_date');
    }

    public function getEndDateAttribute(): ?string
    {
        return $this->meta('_end_date');
    }

    /*
    |--------------------------------------------------------------------------
    | Content Access — the paywall core
    |--------------------------------------------------------------------------
    */

    /**
     * Check if this membership grants access to a specific post.
     */
    public function canAccess(int $postId): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        $plan = $this->plan;
        if (!$plan) {
            return false;
        }

        return $plan->grantsAccessTo($postId);
    }

    public function isActive(): bool
    {
        return in_array($this->post_status, self::ACTIVE_STATUSES, true);
    }

    public function activate(): bool
    {
        $this->post_status = self::STATUS_ACTIVE;
        $this->setMeta('_start_date', current_time('mysql'));
        $this->save();
        do_action('wc_memberships_user_membership_activated', $this->ID);
        return true;
    }

    public function cancel(): bool
    {
        $this->post_status = self::STATUS_CANCELLED;
        $this->save();
        do_action('wc_memberships_user_membership_cancelled', $this->ID);
        return true;
    }

    public function expire(): bool
    {
        $this->post_status = self::STATUS_EXPIRED;
        $this->setMeta('_end_date', current_time('mysql'));
        $this->save();
        do_action('wc_memberships_user_membership_ended', $this->ID);
        return true;
    }
}

