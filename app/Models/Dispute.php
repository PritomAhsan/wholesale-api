<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Dispute extends Model
{
    public const REASONS = [
        'not_received',
        'damaged',
        'wrong_item',
        'quantity_mismatch',
        'counterfeit',
        'late_shipment',
        'seller_not_responding',
        'refund_not_received',
        'other',
    ];

    public const RESOLUTIONS = [
        'refund_full',
        'refund_partial',
        'replacement',
        'no_action',
    ];

    protected $fillable = [

        'uuid',

        'seller_order_id',

        'user_id',

        'reason',

        'description',

        'status',

        'resolution',

        'resolution_amount',

        'resolution_note',

        'resolved_at',

        'resolved_by',

    ];

    protected $casts = [

        'resolution_amount' => 'decimal:2',

        'resolved_at' => 'datetime',

    ];

    protected static function booted(): void
    {
        static::creating(function ($dispute) {

            if (empty($dispute->uuid)) {
                $dispute->uuid = (string) Str::uuid();
            }

        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function sellerOrder()
    {
        return $this->belongsTo(SellerOrder::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function images()
    {
        return $this->hasMany(DisputeImage::class);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }
}
