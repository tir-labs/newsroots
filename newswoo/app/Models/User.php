<?php
/**
 * NewsWoo User — Eloquent model.
 *
 * Maps to wp_users. Bridges WordPress users with Eloquent.
 *
 * @package NewsWoo
 */

namespace NewsWoo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{HasMany};

class User extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = ['user_login', 'user_email', 'user_pass', 'display_name'];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'post_author');
    }

    public function activeSubscriptions(): HasMany
    {
        return $this->subscriptions()->active();
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class, 'post_author');
    }

    public function activeMemberships(): HasMany
    {
        return $this->memberships()->active();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'post_author');
    }

    public function paidOrders(): HasMany
    {
        return $this->orders()->paid();
    }

    /*
    |--------------------------------------------------------------------------
    | Computed
    |--------------------------------------------------------------------------
    */

    public function isSubscriber(): bool
    {
        return $this->activeSubscriptions()->exists();
    }

    public function hasAccessTo(int $postId): bool
    {
        foreach ($this->activeMemberships as $membership) {
            if ($membership->canAccess($postId)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get all restricted post IDs the user has access to.
     */
    public function accessiblePostIds(): array
    {
        $ids = [];
        foreach ($this->activeMemberships as $membership) {
            if ($membership->plan) {
                $ids = array_merge($ids, $membership->plan->getRestrictedPostIds());
            }
        }
        return array_unique($ids);
    }
}

