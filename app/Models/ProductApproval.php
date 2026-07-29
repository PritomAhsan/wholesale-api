<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProductApproval extends Model
{
    protected $fillable = [

        'uuid',

        'product_id',

        'reviewer_id',

        'action',

        'decision',

        'status_before',

        'status_after',

        'remarks',

        'reviewed_at',

    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($approval) {

            if (empty($approval->uuid)) {

                $approval->uuid = (string) Str::uuid();

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

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
