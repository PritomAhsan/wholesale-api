<?php

namespace App\Http\Resources\ProductAttribute;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductAttributeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'uuid' => $this->uuid,

            'category' => $this->whenLoaded(
                'category',
                fn () => [
                    'id' => $this->category?->id,
                    'name' => $this->category?->name,
                ]
            ),

            'name' => $this->name,

            'slug' => $this->slug,

            'type' => $this->type,

            'is_filterable' => $this->is_filterable,

            'is_required' => $this->is_required,

            'status' => $this->status,

            'sort_order' => $this->sort_order,

            'values_count' => $this->values_count ?? $this->values()->count(),

            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,
        ];
    }
}
