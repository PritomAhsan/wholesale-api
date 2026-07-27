<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantAttributeValue extends Model
{
    protected $fillable = [

        'product_variant_id',

        'attribute_id',

        'attribute_value_id',

    ];

    public function variant()
    {
        return $this->belongsTo(
            ProductVariant::class,
            'product_variant_id'
        );
    }

    public function attribute()
    {
        return $this->belongsTo(
            ProductAttribute::class,
            'attribute_id'
        );
    }

    public function value()
    {
        return $this->belongsTo(
            ProductAttributeValue::class,
            'attribute_value_id'
        );
    }
}
