<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProductImage extends Model
{
    use SoftDeletes;

    protected $fillable = [

        'uuid',

        'product_id',

        'image',

        'alt_text',

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

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . $this->image);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
