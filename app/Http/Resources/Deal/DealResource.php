<?php

namespace App\Http\Resources\Deal;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DealResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'uuid' => $this->uuid,

            'type' => $this->type,

            'title' => $this->title,

            'description' => $this->description,

            'discount_percent' => $this->discount_percent,

            'discount_price' => $this->discount_price,

            'min_quantity' => $this->min_quantity,

            'starts_at' => $this->starts_at,

            'ends_at' => $this->ends_at,

            'product' => $this->whenLoaded('product', function () {

                return [

                    'uuid' => $this->product->uuid,

                    'name' => $this->product->name,

                    'slug' => $this->product->slug,

                    'selling_price' => $this->product->selling_price,

                    'currency' => $this->product->currency,

                    'image' => $this->product->primaryImage?->image_url,

                ];

            }),

        ];
    }
}
