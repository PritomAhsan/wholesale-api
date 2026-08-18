<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Supplier extends Model
{
    use SoftDeletes;

    /**
     * Applied when a supplier's own commission_rate is null — an
     * unset rate must mean "not yet configured," never "0%."
     */
    public const DEFAULT_COMMISSION_RATE = 10.00;

    protected $fillable = [

        'uuid',

        'seller_id',

        'user_id',

        'company_name',

        'company_slug',

        'business_type',

        'contact_person',

        'email',

        'phone',

        'website',

        'registration_number',

        'tax_number',

        'description',

        'fulfillment_region',

        'typical_lead_time',

        'commission_rate',

        'logo',

        'banner',

        'status',

        'approved_at',

        'approved_by',
    ];

    protected $casts = [

        'approved_at' => 'datetime',

        'commission_rate' => 'decimal:2',

    ];

    protected static function booted(): void
    {
        static::creating(function ($supplier) {

            $supplier->uuid = (string) Str::uuid();

            if (empty($supplier->company_slug)) {

                $supplier->company_slug = Str::slug(
                    $supplier->company_name
                );
            }

            if (empty($supplier->seller_id)) {

                do {

                    $sellerId = 'BLK-' . strtoupper(Str::random(6));

                } while (static::where('seller_id', $sellerId)->exists());

                $supplier->seller_id = $sellerId;

            }

        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function sellerOrders()
    {
        return $this->hasMany(SellerOrder::class);
    }

    public function storeReviews()
    {
        return $this->hasMany(StoreReview::class);
    }

    public function follows()
    {
        return $this->hasMany(StoreFollow::class);
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class);
    }

    /**
     * The effective commission rate — this supplier's own rate if
     * set, otherwise the platform default. Never treat a null rate
     * as 0%.
     */
    public function getEffectiveCommissionRateAttribute(): float
    {
        return $this->commission_rate !== null
            ? (float) $this->commission_rate
            : self::DEFAULT_COMMISSION_RATE;
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Buyer-facing, non-identifying label. Real company identity
     * (name, contact, email, phone, website) must never reach public
     * storefront responses — buyers should not be able to bypass the
     * platform to order directly from a seller.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->seller_id ?? 'BULKARE Seller';
    }
}
