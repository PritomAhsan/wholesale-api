<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Attribute extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [

        'uuid',

        'category_id',

        'name',

        'slug',

        'type',

        'is_filterable',

        'is_required',

        'status',

        'sort_order',

    ];

    protected $casts = [

        'is_filterable'=>'boolean',

        'is_required'=>'boolean',

        'status'=>'boolean',

    ];

    protected static function booted()
    {
        static::creating(function($attribute){

            $attribute->uuid=(string)Str::uuid();

            $attribute->slug=Str::slug(
                $attribute->name
            );

        });
    }

    public function category()
    {
        return $this->belongsTo(
            Category::class
        );
    }

    public function values()
    {
        return $this->hasMany(
            AttributeValue::class
        )->orderBy('sort_order');
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
