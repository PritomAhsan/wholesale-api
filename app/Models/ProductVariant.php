<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Enums\VariantAvailability;

class ProductVariant extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [

        'product_id',

        'sku',

        'barcode',

        'cost_price',

        'selling_price',

        'compare_at_price',

        'stock_quantity',

        'low_stock_quantity',

        'weight',

        'length',

        'width',

        'height',

        'is_active',

        'is_default',

        'sort_order',

        'wholesale_price',

        'minimum_order_quantity',

        'maximum_order_quantity',

    ];

    protected $casts = [

        'is_active' => 'boolean',

        'is_default' => 'boolean',

        'cost_price' => 'decimal:2',

        'selling_price' => 'decimal:2',

        'compare_at_price' => 'decimal:2',

        'wholesale_price' => 'decimal:2',

        'minimum_order_quantity' => 'decimal:2',

        'maximum_order_quantity' => 'decimal:2',

    ];

    protected $with = [

        'attributeValues.attribute',

        'attributeValues.value',

    ];

    public function product()
    {
        return $this->belongsTo(
            Product::class
        );
    }

    public function attributeValues()
    {
        return $this->hasMany(
            ProductVariantAttributeValue::class
        );
    }

    public function getAvailabilityAttribute(): string
    {
        if ($this->stock_quantity <= 0) {

            return VariantAvailability::OUT_OF_STOCK->value;

        }

        if ($this->stock_quantity <= $this->low_stock_quantity) {

            return VariantAvailability::LOW_STOCK->value;

        }

        return VariantAvailability::IN_STOCK->value;
    }

    public function getMarginAmountAttribute(): float
    {
        return max(
            0,
            (float) $this->selling_price - (float) $this->cost_price
        );
    }

    public function getMarginPercentageAttribute(): float
    {
        if ((float) $this->cost_price <= 0) {

            return 0;

        }

        return round(

            (
                (
                    (float) $this->selling_price
                    -
                    (float) $this->cost_price
                )

                /

                (float) $this->cost_price

            ) * 100,

            2

        );
    }

    public function images()
    {
        return $this->hasMany(ProductVariantImage::class)
            ->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductVariantImage::class)
            ->where('is_primary', true);
    }
}
