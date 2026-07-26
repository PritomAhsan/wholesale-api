<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Unit extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [

        'uuid',

        'name',

        'code',

        'symbol',

        'description',

        'sort_order',

        'status',

    ];

    protected $casts = [

        'status' => 'boolean',

    ];

    protected static function booted()
    {
        static::creating(function ($unit) {

            if (empty($unit->uuid)) {
                $unit->uuid = (string) Str::uuid();
            }

        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
