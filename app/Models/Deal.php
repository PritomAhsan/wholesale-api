<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Deal extends Model
{
    protected $fillable = [

        'uuid',

        'product_id',

        'type',

        'title',

        'description',

        'discount_percent',

        'discount_price',

        'min_quantity',

        'starts_at',

        'ends_at',

        'status',

    ];

    protected $casts = [

        'discount_price' => 'decimal:2',

        'min_quantity' => 'integer',

        'starts_at' => 'datetime',

        'ends_at' => 'datetime',

    ];

    protected static function booted(): void
    {
        static::creating(function ($deal) {

            if (empty($deal->uuid)) {

                $deal->uuid = (string) Str::uuid();

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

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }
}
