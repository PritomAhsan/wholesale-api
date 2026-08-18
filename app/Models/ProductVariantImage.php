<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Support\MediaUrl;

class ProductVariantImage extends Model
{
    use SoftDeletes;

    protected $fillable = [

        'uuid',

        'product_variant_id',

        'image',

        'is_primary',

        'sort_order',

    ];

    protected $casts = [

        'is_primary' => 'boolean',

    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($image) {

            if (empty($image->uuid)) {

                $image->uuid = (string) Str::uuid();

            }

        });
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        return MediaUrl::resolve($this->image);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
