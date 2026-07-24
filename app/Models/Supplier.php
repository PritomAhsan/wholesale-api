<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [

        'uuid',

        'user_id',

        'company_name',

        'company_slug',

        'business_type',

        'contact_person',

        'email',

        'phone',

        'website',

        'registration_number',

        'tax_number',

        'description',

        'logo',

        'banner',

        'status',

        'approved_at',

        'approved_by',
    ];

    protected $casts = [

        'approved_at' => 'datetime',

    ];

    protected static function booted(): void
    {
        static::creating(function ($supplier) {

            $supplier->uuid = (string) Str::uuid();

            if (empty($supplier->company_slug)) {

                $supplier->company_slug = Str::slug(
                    $supplier->company_name
                );
            }

        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }
}
