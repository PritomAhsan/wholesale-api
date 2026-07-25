<?php

namespace App\Http\Resources\ProductAttribute;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductAttributeValueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'uuid' => $this->uuid,

            'attribute' => [

                'uuid' => $this->attribute->uuid,

                'name' => $this->attribute->name,

            ],

            'value' => $this->value,

            'sort_order' => $this->sort_order,

            'status' => $this->status,

            'created_at' => $this->created_at,

        ];
    }
}
