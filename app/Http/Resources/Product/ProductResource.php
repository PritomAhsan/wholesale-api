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

            'uuid' => $this->uuid,

            'name' => $this->name,

            'slug' => $this->slug,

            'sku' => $this->sku,

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            'supplier' => $this->whenLoaded('supplier', function () {

                return [

                    'uuid' => $this->supplier->uuid,

                    'company_name' => $this->supplier->company_name,

                ];

            }),

            'brand' => $this->whenLoaded('brand', function () {

                return [

                    'uuid' => $this->brand->uuid,

                    'name' => $this->brand->name,

                ];

            }),

            'unit' => $this->whenLoaded('unit', function () {

                return [

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

            'primary_image' => $this->when(
                isset($this->primary_image),
                $this->primary_image
            ),

            'gallery' => $this->when(
                isset($this->gallery),
                $this->gallery
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
