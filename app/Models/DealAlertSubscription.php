<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DealAlertSubscription extends Model
{
    protected $fillable = [

        'uuid',

        'email',

        'subscribed_at',

    ];

    protected $casts = [

        'subscribed_at' => 'datetime',

    ];

    protected static function booted(): void
    {
        static::creating(function ($subscription) {

            if (empty($subscription->uuid)) {

                $subscription->uuid = (string) Str::uuid();

            }

        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
