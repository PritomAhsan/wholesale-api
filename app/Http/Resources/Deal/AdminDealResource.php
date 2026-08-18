<?php

namespace App\Http\Resources\Deal;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminDealResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'uuid' => $this->uuid,

            'product_id' => $this->product_id,

            'type' => $this->type,

            'title' => $this->title,

            'description' => $this->description,

            'discount_percent' => $this->discount_percent,

            'discount_price' => $this->discount_price,

            'min_quantity' => $this->min_quantity,

            'starts_at' => $this->starts_at,

            'ends_at' => $this->ends_at,

            'status' => $this->status,

            'product' => $this->whenLoaded('product', function () {

                return [

                    'id' => $this->product->id,

                    'uuid' => $this->product->uuid,

                    'name' => $this->product->name,

                    'selling_price' => $this->product->selling_price,

                ];

            }),

            'created_at' => $this->created_at,

        ];
    }
}
