<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SellerOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [

        'uuid',

        'seller_order_number',

        'order_id',

        'supplier_id',

        'subtotal',

        'commission_amount',

        'payable_amount',

        'payout_id',

        'status',

        'cancelled_at',

        'tracking_number',

        'shipping_carrier',

        'shipped_at',

        'delivered_at',

    ];

    protected $casts = [

        'subtotal' => 'decimal:2',

        'commission_amount' => 'decimal:2',

        'payable_amount' => 'decimal:2',

        'shipped_at' => 'datetime',

        'delivered_at' => 'datetime',

        'cancelled_at' => 'datetime',

    ];

    protected static function booted(): void
    {
        static::creating(function ($sellerOrder) {

            if (empty($sellerOrder->uuid)) {
                $sellerOrder->uuid = (string) Str::uuid();
            }

        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payout()
    {
        return $this->belongsTo(Payout::class);
    }

    public function disputes()
    {
        return $this->hasMany(Dispute::class);
    }
}
