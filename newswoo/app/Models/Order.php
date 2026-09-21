<?php
/**
 * NewsWoo Order — Eloquent model.
 *
 * Maps to wp_posts (post_type: 'shop_order') with meta.
 * Replaces WC_Order + WC_Order_Data_Store_CPT.
 *
 * @package NewsWoo
 */

namespace NewsWoo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Order extends Model
{
    protected $table = 'posts';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    const STATUS_PENDING    = 'wc-pending';
    const STATUS_PROCESSING = 'wc-processing';
    const STATUS_COMPLETED  = 'wc-completed';
    const STATUS_ON_HOLD    = 'wc-on-hold';
    const STATUS_CANCELLED  = 'wc-cancelled';
    const STATUS_REFUNDED   = 'wc-refunded';
    const STATUS_FAILED     = 'wc-failed';

    const PAID_STATUSES = [self::STATUS_PROCESSING, self::STATUS_COMPLETED];

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePaid($query)
    {
        return $query->whereIn('post_status', self::PAID_STATUSES);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('post_author', $userId);
    }

    public function scopeCompleted($query)
    {
        return $query->where('post_status', self::STATUS_COMPLETED);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'post_author');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'post_parent');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'post_parent');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
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

    public function getTotalAttribute(): float
    {
        return (float) $this->meta('_order_total', 0);
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

    public function getBillingEmailAttribute(): string
    {
        return $this->meta('_billing_email', '');
    }

    public function getBillingFirstNameAttribute(): string
    {
        return $this->meta('_billing_first_name', '');
    }

    public function getBillingLastNameAttribute(): string
    {
        return $this->meta('_billing_last_name', '');
    }

    public function getBillingCountryAttribute(): string
    {
        return $this->meta('_billing_country', '');
    }

    public function getDatePaidAttribute(): ?string
    {
        return $this->meta('_date_paid');
    }

    // --- Computed ---

    public function isPaid(): bool
    {
        return in_array($this->post_status, self::PAID_STATUSES, true);
    }

    public function formattedTotal(): string
    {
        return wc_price($this->total);
    }
}

