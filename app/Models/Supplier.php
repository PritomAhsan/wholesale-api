<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Supplier extends Model
{
    use SoftDeletes;

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

        'logo',

        'banner',

        'status',

        'approved_at',

        'approved_by',
    ];

    protected $casts = [

        'approved_at' => 'datetime',

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
