<?php

namespace App\Http\Resources\Brand;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'uuid' => $this->uuid,

            'name' => $this->name,

            'slug' => $this->slug,

            'description' => $this->description,

            'website' => $this->website,

            'logo' => $this->logo_url,

            'featured' => (bool) $this->featured,

            'status' => (bool) $this->status,

            // 'products_count' => $this->whenLoaded(
            //     'products',
            //     fn () => $this->products->count(),
            //     $this->products()->count()
            // ),

            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,

        ];
    }
}
