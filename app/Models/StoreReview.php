<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class StoreReview extends Model
{
    protected $fillable = [

        'uuid',

        'supplier_id',

        'user_id',

        'seller_order_id',

        'rating',

        'communication_rating',

        'shipping_rating',

        'packaging_rating',

        'comment',

        'status',

    ];

    protected $casts = [

        'rating' => 'integer',

        'communication_rating' => 'integer',

        'shipping_rating' => 'integer',

        'packaging_rating' => 'integer',

    ];

    protected static function booted(): void
    {
        static::creating(function ($review) {

            if (empty($review->uuid)) {
                $review->uuid = (string) Str::uuid();
            }

        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sellerOrder()
    {
        return $this->belongsTo(SellerOrder::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
