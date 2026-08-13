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
        return 'BULKARE Seller #' . strtoupper(substr($this->uuid, 0, 6));
    }
}
