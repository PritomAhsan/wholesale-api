<?php

namespace App\Http\Resources\Supplier;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'uuid' => $this->uuid,

            'company_name' => $this->company_name,

            'company_slug' => $this->company_slug,

            'business_type' => $this->business_type,

            'contact_person' => $this->contact_person,

            'email' => $this->email,

            'phone' => $this->phone,

            'website' => $this->website,

            'description' => $this->description,

            'logo' => $this->logo
                ? asset('storage/' . $this->logo)
                : null,

            'banner' => $this->banner
                ? asset('storage/' . $this->banner)
                : null,

            'status' => $this->status,

            'products_count' => $this->whenCounted('products'),

            'created_at' => $this->created_at,

        ];
    }
}
