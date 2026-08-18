<?php

namespace App\Http\Resources\Category;

use App\Support\MediaUrl;
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

            'parent_name' => $this->whenLoaded('parent', fn () => $this->parent?->name),

            'name' => $this->name,

            'slug' => $this->slug,

            'description' => $this->description,

            'image' => MediaUrl::resolve($this->image),

            'icon' => MediaUrl::resolve($this->icon),

            'sort_order' => $this->sort_order,

            'status' => $this->status,

            'children_count' => $this->children()->count(),

            'children' => $this->whenLoaded(
                'children',
                fn () => CategoryResource::collection($this->children)
            ),

            'products_count' => $this->whenCounted(
                'products',
                null,
                0
            ),

            'created_at' => $this->created_at,

        ];
    }
}
