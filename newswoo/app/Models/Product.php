<?php
/**
 * NewsWoo Product — Eloquent model.
 *
 * Maps to wp_posts (post_type: 'product' or 'product_subscription') with meta.
 * In NewsWoo, products are subscription plans.
 *
 * @package NewsWoo
 */

namespace NewsWoo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{HasMany};

class Product extends Model
{
    protected $table = 'posts';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeSubscription($query)
    {
        return $query->where('post_type', 'product_subscription');
    }

    public function scopePublished($query)
    {
        return $query->where('post_status', 'publish');
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'post_parent');
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

    public function getPriceAttribute(): float
    {
        return (float) $this->meta('_price', 0);
    }

    public function getRegularPriceAttribute(): float
    {
        return (float) $this->meta('_regular_price', 0);
    }

    public function getSkuAttribute(): string
    {
        return $this->meta('_sku', '');
    }

    public function getBillingPeriodAttribute(): string
    {
        return $this->meta('_subscription_period', 'month');
    }

    public function getBillingIntervalAttribute(): int
    {
        return (int) $this->meta('_subscription_period_interval', 1);
    }

    public function getTrialPeriodAttribute(): string
    {
        return $this->meta('_subscription_trial_period', '');
    }

    public function getTrialLengthAttribute(): int
    {
        return (int) $this->meta('_subscription_trial_length', 0);
    }

    public function getSignUpFeeAttribute(): float
    {
        return (float) $this->meta('_subscription_sign_up_fee', 0);
    }

    public function isSubscription(): bool
    {
        return $this->post_type === 'product_subscription';
    }

    public function formattedPrice(): string
    {
        $price = wc_price($this->price);
        return $price . ' / ' . $this->billing_interval . ' ' . $this->billing_period;
    }
}

