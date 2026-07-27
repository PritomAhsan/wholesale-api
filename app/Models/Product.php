<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Enums\ProductStatus;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [

        'uuid',

        'supplier_id',

        'brand_id',

        'unit_id',

        'name',

        'slug',

        'sku',

        'short_description',

        'description',

        'cost_price',

        'selling_price',

        'compare_at_price',

        'currency',

        'min_order_quantity',

        'max_order_quantity',

        'stock_quantity',

        'weight',

        'length',

        'width',

        'height',

        'featured',

        'is_digital',

        'requires_shipping',

        'status',

        'approved_at',

        'approved_by',

        'published_at',

        'meta_title',

        'meta_description',

        'meta_keywords',

    ];

    protected $casts = [

        'status' => ProductStatus::class,

        'featured' => 'boolean',

        'is_digital' => 'boolean',

        'requires_shipping' => 'boolean',

        'approved_at' => 'datetime',

        'published_at' => 'datetime',

        'cost_price' => 'decimal:2',

        'selling_price' => 'decimal:2',

        'compare_at_price' => 'decimal:2',

        'min_order_quantity' => 'decimal:2',

        'max_order_quantity' => 'decimal:2',

        'weight' => 'decimal:2',

        'length' => 'decimal:2',

        'width' => 'decimal:2',

        'height' => 'decimal:2',

    ];

    protected static function booted()
    {
        static::creating(function ($product) {

            if (empty($product->uuid)) {

                $product->uuid = (string) Str::uuid();

            }

            if (empty($product->slug)) {

                $product->slug = Str::slug(

                    $product->name

                );

            }

            if (empty($product->sku)) {

                $product->sku = strtoupper(

                    Str::random(10)

                );

            }

        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeDraft($query)
    {
        return $query->where(
            'status',
            ProductStatus::DRAFT
        );
    }

    public function scopePending($query)
    {
        return $query->where(
            'status',
            ProductStatus::PENDING
        );
    }

    public function scopeApproved($query)
    {
        return $query->where(
            'status',
            ProductStatus::APPROVED
        );
    }

    public function scopePublished($query)
    {
        return $query->where(
            'status',
            ProductStatus::PUBLISHED
        );
    }

    public function scopeRejected($query)
    {
        return $query->where(
            'status',
            ProductStatus::REJECTED
        );
    }

    public function scopeArchived($query)
    {
        return $query->where(
            'status',
            ProductStatus::ARCHIVED
        );
    }

    public function scopeFeatured($query)
    {
        return $query->where(
            'featured',
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getIsPublishedAttribute(): bool
    {
        return $this->status === 'published';
    }

    public function getIsApprovedAttribute(): bool
    {
        return in_array(
            $this->status,
            ['approved', 'published']
        );
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format(
            $this->selling_price,
            2
        );
    }

    public function getDiscountPercentageAttribute(): int
    {
        if (

            ! $this->compare_at_price ||

            $this->compare_at_price <= 0 ||

            $this->compare_at_price <= $this->selling_price

        ) {

            return 0;

        }

        return (int) round(

            (

                ($this->compare_at_price - $this->selling_price)

                /

                $this->compare_at_price

            ) * 100

        );
    }

    public function getInStockAttribute(): bool
    {
        return $this->stock_quantity > 0;
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }

    public function categories()
    {
        return $this->belongsToMany(

            Category::class,

            'product_categories'

        )->withTimestamps();
    }

    public function assignedAttributes()
    {
        return $this->hasMany(
            ProductAssignedAttribute::class
        );
    }

    public function variants()
    {
        return $this->hasMany(
            ProductVariant::class
        );
    }
}
