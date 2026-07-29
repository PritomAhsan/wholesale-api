<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class InventoryTransaction extends Model
{
    protected $fillable = [

        'uuid',

        'product_variant_id',

        'transaction_type',

        'movement_type',

        'quantity',

        'stock_before',

        'stock_after',

        'reference_type',

        'reference_id',

        'remarks',

        'created_by',

    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {

            if (empty($transaction->uuid)) {

                $transaction->uuid = (string) Str::uuid();

            }

        });

    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
