<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Scaffolding only — no payout gateway (Stripe Connect) is wired up
 * yet. A supplier requests a payout for their delivered, not-yet-
 * paid-out seller orders; an admin marks it paid manually once the
 * transfer is actually sent outside this system.
 */
class Payout extends Model
{
    public const STATUSES = ['requested', 'processing', 'paid', 'failed'];

    protected $fillable = [

        'uuid',

        'supplier_id',

        'amount',

        'status',

        'requested_at',

        'paid_at',

        'paid_by',

        'reference_note',

    ];

    protected $casts = [

        'amount' => 'decimal:2',

        'requested_at' => 'datetime',

        'paid_at' => 'datetime',

    ];

    protected static function booted(): void
    {
        static::creating(function ($payout) {

            if (empty($payout->uuid)) {
                $payout->uuid = (string) Str::uuid();
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

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function sellerOrders()
    {
        return $this->hasMany(SellerOrder::class);
    }
}
