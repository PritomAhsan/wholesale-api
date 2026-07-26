<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [

        'uuid',

        'parent_id',

        'name',

        'slug',

        'description',

        'image',

        'icon',

        'sort_order',

        'status',

    ];

    protected $casts = [

        'status' => 'boolean',

    ];

    protected static function booted(): void
    {
        static::creating(function ($category) {

            if (empty($category->uuid)) {
                $category->uuid = (string) Str::uuid();
            }

            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }

        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function parent()
    {
        return $this->belongsTo(
            Category::class,
            'parent_id'
        );
    }

    public function children()
    {
        return $this->hasMany(
            Category::class,
            'parent_id'
        )->orderBy('sort_order');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function resolveRouteBinding($value, $field = null)
    {
        if ($field === 'slug') {
            return $this->where('slug', $value)->firstOrFail();
        }

        return parent::resolveRouteBinding($value, $field);
    }
}
