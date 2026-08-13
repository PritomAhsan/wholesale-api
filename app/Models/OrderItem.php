<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OrderItem extends Model
{
    protected $fillable = [

        'uuid',

        'seller_order_id',

        'product_id',

        'product_variant_id',

        'product_name',

        'product_sku',

        'product_image',

        'unit_price',

        'quantity',

        'line_total',

    ];

    protected $casts = [

        'unit_price' => 'decimal:2',

        'line_total' => 'decimal:2',

    ];

    protected static function booted(): void
    {
        static::creating(function ($item) {

            if (empty($item->uuid)) {
                $item->uuid = (string) Str::uuid();
            }

        });
    }

    public function sellerOrder()
    {
        return $this->belongsTo(SellerOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
