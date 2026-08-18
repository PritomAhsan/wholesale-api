<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */
            'id' => $this->id,

            'uuid' => $this->uuid,

            'name' => $this->name,

            'slug' => $this->slug,

            'sku' => $this->sku,

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            // Buyers only ever see an anonymized seller label — real
            // company identity is reserved for admin requests so buyers
            // can't bypass the platform to order directly from a seller.
            'supplier' => $this->whenLoaded('supplier', function () {

                $data = [

                    'id' => $this->supplier->id,

                    'uuid' => $this->supplier->uuid,

                    'seller_id' => $this->supplier->seller_id,

                    'display_name' => $this->supplier->display_name,

                ];

                // The public product route carries no auth middleware, so
                // Sanctum never parses the bearer token unless the guard
                // is resolved explicitly here.
                if (auth('sanctum')->user()?->hasAnyRole(['Super Admin', 'Admin'])) {

                    $data['company_name'] = $this->supplier->company_name;

                }

                return $data;

            }),

            'brand' => $this->whenLoaded('brand', function () {

                return [

                    'id' => $this->brand->id,

                    'uuid' => $this->brand->uuid,

                    'name' => $this->brand->name,

                ];

            }),

            'unit' => $this->whenLoaded('unit', function () {

                return [

                    'id' => $this->unit->id,

                    'uuid' => $this->unit->uuid,

                    'name' => $this->unit->name,

                    'symbol' => $this->unit->symbol,

                ];

            }),

            'categories' => $this->whenLoaded(

                'categories',

                function () {

                    return $this->categories->map(

                        function ($category) {

                            return [

                                'id' => $category->id,

                                'uuid' => $category->uuid,

                                'name' => $category->name,

                                'slug' => $category->slug,

                            ];

                        }

                    );

                }

            ),

            'attributes' => $this->whenLoaded(
                'assignedAttributes',
                function () {

                    return $this->assignedAttributes->map(

                        function ($item) {

                            return [

                                'attribute' => [

                                    'id' => $item->attribute->id,

                                    'name' => $item->attribute->name,

                                ],

                                'value' => [

                                    'id' => $item->value->id,

                                    'value' => $item->value->value,

                                ],

                            ];

                        }

                    );

                }
            ),

            /*
            |--------------------------------------------------------------------------
            | Description
            |--------------------------------------------------------------------------
            */

            'short_description' => $this->short_description,

            'description' => $this->description,

            /*
            |--------------------------------------------------------------------------
            | Pricing
            |--------------------------------------------------------------------------
            */

            'cost_price' => $this->cost_price,

            'selling_price' => $this->selling_price,

            'compare_at_price' => $this->compare_at_price,

            'formatted_price' => $this->formatted_price,

            'discount_percentage' => $this->discount_percentage,

            'currency' => $this->currency,

            'average_rating' => $this->reviews_avg_rating
                ? round((float) $this->reviews_avg_rating, 1)
                : null,

            'reviews_count' => $this->reviews_count ?? 0,

            // Quantity price breaks — only present when a real bulk deal
            // has been authored for this product, never fabricated.
            'price_tiers' => $this->whenLoaded('deals', function () {

                return $this->deals
                    ->where('type', 'bulk')
                    ->where('status', 'active')
                    ->sortBy('min_quantity')
                    ->values()
                    ->map(function ($deal) {

                        return [

                            'min_quantity' => $deal->min_quantity,

                            'discount_percent' => $deal->discount_percent,

                            'discount_price' => $deal->discount_price,

                        ];

                    });

            }),

            /*
            |--------------------------------------------------------------------------
            | Inventory
            |--------------------------------------------------------------------------
            */

            'stock_quantity' => $this->stock_quantity,

            'in_stock' => $this->in_stock,

            'min_order_quantity' => $this->min_order_quantity,

            'max_order_quantity' => $this->max_order_quantity,

            /*
            |--------------------------------------------------------------------------
            | Dimensions
            |--------------------------------------------------------------------------
            */

            'weight' => $this->weight,

            'length' => $this->length,

            'width' => $this->width,

            'height' => $this->height,

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            'status' => $this->status?->value,

            'status_label' => $this->status_label,

            'featured' => $this->featured,

            'is_digital' => $this->is_digital,

            'requires_shipping' => $this->requires_shipping,

            /*
            |--------------------------------------------------------------------------
            | SEO
            |--------------------------------------------------------------------------
            */

            'meta_title' => $this->meta_title,

            'meta_description' => $this->meta_description,

            'meta_keywords' => $this->meta_keywords,

            /*
            |--------------------------------------------------------------------------
            | Images
            |--------------------------------------------------------------------------
            | These will be populated in Batch 6.4
            */

            'images' => $this->whenLoaded(
                'images',
                function () {
                    return $this->images->map(
                        function ($image) {
                            return [

                                'id' => $image->id,

                                'uuid' => $image->uuid,

                                'image' => $image->image_url,

                                'alt_text' => $image->alt_text,

                                'is_primary' => (bool) $image->is_primary,

                                'sort_order' => $image->sort_order,

                            ];
                        }
                    );
                }
            ),

            'variants' => $this->whenLoaded(
                'variants',
                function () {
                    return $this->variants->map(
                        function ($variant) {
                            return [

                                'id' => $variant->id,

                                'uuid' => $variant->uuid,

                                'sku' => $variant->sku,

                                'barcode' => $variant->barcode,

                                'cost_price' => $variant->cost_price,

                                'selling_price' => $variant->selling_price,

                                'compare_at_price' => $variant->compare_at_price,

                                'wholesale_price' => $variant->wholesale_price,

                                'stock_quantity' => $variant->stock_quantity,

                                'low_stock_quantity' => $variant->low_stock_quantity,

                                'minimum_order_quantity' => $variant->minimum_order_quantity,

                                'maximum_order_quantity' => $variant->maximum_order_quantity,

                                'weight' => $variant->weight,

                                'length' => $variant->length,

                                'width' => $variant->width,

                                'height' => $variant->height,

                                'is_active' => (bool) $variant->is_active,

                                'is_default' => (bool) $variant->is_default,

                                'sort_order' => $variant->sort_order,

                                'availability' => $variant->availability,

                                'attributes' => $variant->relationLoaded('attributeValues')
                                    ? $variant->attributeValues->map(
                                        function ($assignment) {
                                            return [

                                                'attribute_id' => $assignment->attribute_id,

                                                'attribute_value_id' => $assignment->attribute_value_id,

                                                'attribute_name' => $assignment->attribute?->name,

                                                'value' => $assignment->value?->value,

                                            ];
                                        }
                                    )
                                    : [],

                                'images' => $variant->relationLoaded('images')
                                    ? $variant->images->map(
                                        function ($image) {
                                            return [

                                                'id' => $image->id,
                                                'uuid' => $image->uuid,

                                                'image' => $image->image_url,
                                                'image_url' => $image->image_url,

                                                'is_primary' => (bool) $image->is_primary,

                                                'sort_order' => $image->sort_order,
                                            ];
                                        }
                                    )
                                    : [],

                            ];
                        }
                    );
                }
            ),

            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */

            'approved_at' => $this->approved_at,

            'published_at' => $this->published_at,

            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,

        ];
    }
}
