<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'uuid' => $this->uuid,

            'name' => $this->name,

            'slug' => $this->slug,

            'sku' => $this->sku,

            'status' => $this->status?->value,

            'status_label' => $this->status_label,

            'featured' => (bool) $this->featured,

            'price' => $this->price,

            'selling_price' => $this->selling_price,

            'formatted_price' => $this->formatted_price,

            'min_order_quantity' => $this->min_order_quantity,

            'supplier' => [

                'uuid' => $this->supplier?->uuid,

                'display_name' => $this->supplier?->display_name,

            ],

            'brand' => [

                'uuid' => $this->brand?->uuid,

                'name' => $this->brand?->name,

            ],

            'categories' => $this->categories
                ->pluck('name')
                ->values(),

            'stock' => $this->variants_count > 0
                ? ($this->variants_sum_stock_quantity ?? 0)
                : $this->stock_quantity,

            'variants_count' => $this->variants_count,

            'created_at' => $this->created_at,

            'is_publishable' => $this->status === \App\Enums\ProductStatus::APPROVED,

            'primary_image' => optional(
                $this->images
                    ->where('is_primary', true)
                    ->first()
            )->image_url,

            'approval_count' => $this->whenCounted(
                'approvals'
            ),

            'last_updated' => $this->updated_at,

        ];
    }
}
