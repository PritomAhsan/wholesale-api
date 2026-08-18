<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Scaffolding only — no payment gateway is wired up yet. A row is
 * created at checkout time so the amount owed is recorded from day
 * one, but nothing here actually collects money; status stays
 * 'pending' until a real gateway integration exists to move it.
 */
class Payment extends Model
{
    public const STATUSES = ['pending', 'paid', 'failed', 'refunded'];

    protected $fillable = [

        'uuid',

        'order_id',

        'amount',

        'currency',

        'method',

        'status',

        'provider_reference',

        'paid_at',

    ];

    protected $casts = [

        'amount' => 'decimal:2',

        'paid_at' => 'datetime',

    ];

    protected static function booted(): void
    {
        static::creating(function ($payment) {

            if (empty($payment->uuid)) {
                $payment->uuid = (string) Str::uuid();
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
}
