<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProductStatusHistory extends Model
{
    protected $fillable = [

        'uuid',

        'product_id',

        'user_id',

        'action',

        'status_before',

        'status_after',

        'remarks',

        'performed_at',

    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($history) {

            if (!$history->uuid) {

                $history->uuid=(string) Str::uuid();

            }

        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
