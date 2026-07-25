<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Brand extends Model
{
    use SoftDeletes;

    protected $fillable = [

        'uuid',

        'name',

        'slug',

        'description',

        'logo',

        'website',

        'featured',

        'status',

    ];

    protected $casts = [

        'featured' => 'boolean',

        'status' => 'boolean',

    ];

    protected static function booted(): void
    {
        static::creating(function ($brand) {

            if (empty($brand->uuid)) {
                $brand->uuid = (string) Str::uuid();
            }

            if (empty($brand->slug)) {
                $brand->slug = Str::slug($brand->name);
            }

        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // public function products()
    // {
    //     return $this->hasMany(Product::class);
    // }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo) {
            return null;
        }

        return Storage::disk('public')->url(
            $this->logo
        );
    }
}
