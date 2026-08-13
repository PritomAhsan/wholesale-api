<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Rfq extends Model
{
    use SoftDeletes;

    protected $fillable = [

        'uuid',

        'user_id',

        'supplier_id',

        'product_id',

        'product_name',

        'preferred_supplier_name',

        'quantity',

        'unit',

        'budget',

        'destination_country',

        'required_delivery_date',

        'message',

        'admin_response',

        'attachment_path',

        'contact_name',

        'contact_email',

        'contact_phone',

        'status',

        'responded_at',

        'responded_by',

    ];

    protected $casts = [

        'quantity' => 'decimal:2',

        'budget' => 'decimal:2',

        'required_delivery_date' => 'date',

        'responded_at' => 'datetime',

    ];

    protected static function booted(): void
    {
        static::creating(function ($rfq) {

            if (empty($rfq->uuid)) {

                $rfq->uuid = (string) Str::uuid();

            }

        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function responder()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }
}
