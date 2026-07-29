<?php

namespace App\Http\Resources\ProductImage;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'uuid' => $this->uuid,

            'image' => $this->image,

            'image_url' => $this->image_url,

            'alt_text' => $this->alt_text,

            'is_primary' => $this->is_primary,

            'sort_order' => $this->sort_order,

            'created_at' => $this->created_at,

        ];
    }
}
