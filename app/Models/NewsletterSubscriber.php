<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewsletterSubscriber extends Model
{
    protected $fillable = [

        'uuid',

        'email',

        'topics',

        'frequency',

        'subscribed_at',

        'unsubscribed_at',

    ];

    protected $casts = [

        'topics' => 'array',

        'subscribed_at' => 'datetime',

        'unsubscribed_at' => 'datetime',

    ];

    protected static function booted(): void
    {
        static::creating(function ($subscriber) {

            if (empty($subscriber->uuid)) {

                $subscriber->uuid = (string) Str::uuid();

            }

        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
