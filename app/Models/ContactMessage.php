<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ContactMessage extends Model
{
    protected $fillable = [

        'uuid',

        'topic',

        'name',

        'business_email',

        'account_email',

        'reference_number',

        'message',

        'attachment_path',

        'status',

    ];

    protected static function booted(): void
    {
        static::creating(function ($contactMessage) {

            if (empty($contactMessage->uuid)) {

                $contactMessage->uuid = (string) Str::uuid();

            }

        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
