<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Review extends Model
{
    protected $fillable = [

        'uuid',

        'product_id',

        'user_id',

        'order_item_id',

        'rating',

        'comment',

        'status',

    ];

    protected $casts = [

        'rating' => 'integer',

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

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
