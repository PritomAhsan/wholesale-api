<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class AttributeValue extends Model
{
    use HasFactory;

    protected $fillable=[

        'uuid',

        'attribute_id',

        'value',

        'sort_order',

        'status',

    ];

    protected $casts=[

        'status'=>'boolean',

    ];

    protected static function booted()
    {
        static::creating(function($value){

            $value->uuid=(string)Str::uuid();

        });
    }

    public function attribute()
    {
        return $this->belongsTo(
            Attribute::class
        );
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function assignedProducts()
    {
        return $this->hasMany(
            ProductAssignedAttribute::class,
            'attribute_value_id'
        );
    }

    public function variantValues()
    {
        return $this->hasMany(
            ProductVariantAttributeValue::class,
            'attribute_value_id'
        );
    }
}
