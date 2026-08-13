<?php

namespace App\Http\Resources\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,     

            'uuid' => $this->uuid,

            'parent_id' => $this->parent_id,

            'name' => $this->name,

            'slug' => $this->slug,

            'description' => $this->description,

            'image' => $this->image
                ? asset('storage/' . $this->image)
                : null,

            'icon' => $this->icon
                ? asset('storage/' . $this->icon)
                : null,

            'sort_order' => $this->sort_order,

            'status' => $this->status,

            'children_count' => $this->children()->count(),

            'products_count' => $this->whenCounted(
                'products',
                null,
                0
            ),

            'created_at' => $this->created_at,

        ];
    }
}
