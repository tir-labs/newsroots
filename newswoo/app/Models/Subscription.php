<?php
/**
 * NewsWoo Subscription — Eloquent model.
 *
 * Maps to wp_posts (post_type: 'shop_subscription') with meta via PostMeta.
 * Replaces WC_Subscription + WCS_Subscription_Data_Store_CPT.
 *
 * @package NewsWoo
 */

namespace NewsWoo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasOne};

class Subscription extends Model
{
    protected $table = 'posts';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $postType = 'shop_subscription';

    protected $fillable = [
        'post_author', 'post_date', 'post_date_gmt', 'post_content',
        'post_title', 'post_status', 'post_name', 'post_parent',
        'post_type', 'post_modified', 'post_modified_gmt',
    ];

    // Status constants — matches WooCommerce Subscriptions.
    const STATUS_ACTIVE         = 'wc-active';
    const STATUS_PENDING_CANCEL = 'wc-pending-cancel';
    const STATUS_ON_HOLD        = 'wc-on-hold';
    const STATUS_CANCELLED      = 'wc-cancelled';
    const STATUS_EXPIRED        = 'wc-expired';
    const STATUS_SUSPENDED      = 'wc-suspended';
    const STATUS_SWITCHED       = 'wc-switched';
    const STATUS_TRASH          = 'wc-trash';

    const ACTIVE_STATUSES   = [self::STATUS_ACTIVE, self::STATUS_PENDING_CANCEL];
    const FORMER_STATUSES   = [self::STATUS_ON_HOLD, self::STATUS_CANCELLED, self::STATUS_EXPIRED];

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('post_status', self::STATUS_ACTIVE);
    }

    public function scopePendingCancel($query)
    {
        return $query->where('post_status', self::STATUS_PENDING_CANCEL);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('post_author', $userId);
    }

    public function scopeExpired($query)
    {
        return $query->where('post_status', self::STATUS_EXPIRED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('post_status', self::STATUS_CANCELLED);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * The customer (WordPress user).
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'post_author');
    }

    /**
     * The parent product (subscription plan).
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'post_parent');
    }

    /**
     * All orders related to this subscription (initial, renewals, switches).
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'post_parent')
            ->where('post_type', 'shop_order');
    }

    /**
     * The most recent order.
     */
    public function latestOrder(): HasOne
    {
        return $this->hasOne(Order::class, 'post_parent')
            ->where('post_type', 'shop_order')
            ->latestOfMany();
    }

    /**
     * Associated memberships.
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class, 'post_parent');
    }

    /*
    |--------------------------------------------------------------------------
    | Meta Accessors — maps post_meta to clean properties
    |--------------------------------------------------------------------------
    */

    /**
     * Get a meta value.
     */
    public function meta(string $key, mixed $default = null): mixed
    {
        $value = get_post_meta($this->ID, $key, true);
        return $value === '' ? $default : $value;
    }

    /**
     * Set a meta value.
     */
    public function setMeta(string $key, mixed $value): void
    {
        update_post_meta($this->ID, $key, $value);
    }

    // --- Schedule properties ---

    public function getBillingPeriodAttribute(): string
    {
        return $this->meta('_billing_period', 'month');
    }

    public function getBillingIntervalAttribute(): int
    {
        return (int) $this->meta('_billing_interval', 1);
    }

    public function getScheduleStartAttribute(): ?string
    {
        return $this->meta('_schedule_start');
    }

    public function getScheduleNextPaymentAttribute(): ?string
    {
        return $this->meta('_schedule_next_payment');
    }

    public function getScheduleEndAttribute(): ?string
    {
        return $this->meta('_schedule_end');
    }

    public function getScheduleTrialEndAttribute(): ?string
    {
        return $this->meta('_schedule_trial_end');
    }

    public function getScheduleCancelledAttribute(): ?string
    {
        return $this->meta('_schedule_cancelled');
    }

    public function getPaymentMethodAttribute(): string
    {
        return $this->meta('_payment_method', '');
    }

    public function getPaymentMethodTitleAttribute(): string
    {
        return $this->meta('_payment_method_title', '');
    }

    public function getTransactionIdAttribute(): string
    {
        return $this->meta('_transaction_id', '');
    }

    public function getCustomerIdAttribute(): int
    {
        return (int) $this->meta('_customer_user', 0);
    }

    // --- Computed properties ---

    public function isActive(): bool
    {
        return in_array($this->post_status, self::ACTIVE_STATUSES, true);
    }

    public function isFormer(): bool
    {
        return in_array($this->post_status, self::FORMER_STATUSES, true);
    }

    public function hasTrial(): bool
    {
        return !empty($this->schedule_trial_end);
    }

    public function getRenewalPriceAttribute(): float
    {
        return (float) $this->meta('_order_total', 0);
    }

    /*
    |--------------------------------------------------------------------------
    | Actions — subscription lifecycle
    |--------------------------------------------------------------------------
    */

    public function activate(): bool
    {
        $this->post_status = self::STATUS_ACTIVE;
        $this->save();
        do_action('activated_subscription', $this->ID);
        return true;
    }

    public function cancel(): bool
    {
        $this->post_status = self::STATUS_CANCELLED;
        $this->setMeta('_schedule_cancelled', current_time('mysql'));
        $this->save();
        do_action('cancelled_subscription', $this->ID);
        return true;
    }

    public function suspend(): bool
    {
        $this->post_status = self::STATUS_SUSPENDED;
        $this->save();
        do_action('suspended_subscription', $this->ID);
        return true;
    }

    public function expire(): bool
    {
        $this->post_status = self::STATUS_EXPIRED;
        $this->setMeta('_schedule_end', current_time('mysql'));
        $this->save();
        do_action('expired_subscription', $this->ID);
        return true;
    }

    /**
     * Get the formatted renewal price.
     */
    public function formattedPrice(): string
    {
        return wc_price($this->renewal_price) . ' / ' . $this->billing_interval . ' ' . $this->billing_period;
    }
}

