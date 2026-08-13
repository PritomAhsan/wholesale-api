<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [

        'uuid',

        'order_number',

        'user_id',

        'subtotal',

        'total',

        'currency',

        'status',

        'cancellation_reason',

        'cancelled_at',

        'cancelled_by',

        'shipping_name',

        'shipping_phone',

        'shipping_address',

        'shipping_city',

        'shipping_country',

        'shipping_postal_code',

        'notes',

        'placed_at',

    ];

    protected $casts = [

        'subtotal' => 'decimal:2',

        'total' => 'decimal:2',

        'placed_at' => 'datetime',

        'cancelled_at' => 'datetime',

    ];

    protected static function booted(): void
    {
        static::creating(function ($order) {

            if (empty($order->uuid)) {
                $order->uuid = (string) Str::uuid();
            }

            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(Str::random(8));
            }

        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sellerOrders()
    {
        return $this->hasMany(SellerOrder::class);
    }

    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * An order can only be cancelled while none of its seller orders
     * have shipped yet — once goods are in transit, cancellation has
     * to go through a returns process instead.
     */
    public function isCancellable(): bool
    {
        if (in_array($this->status, ['cancelled', 'completed'], true)) {
            return false;
        }

        return ! $this->sellerOrders
            ->pluck('status')
            ->intersect(['shipped', 'delivered'])
            ->count();
    }
}
