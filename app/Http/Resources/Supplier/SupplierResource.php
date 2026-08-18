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

            'seller_id' => $this->seller_id,

            'company_name' => $this->company_name,

            'company_slug' => $this->company_slug,

            'business_type' => $this->business_type,

            'contact_person' => $this->contact_person,

            'email' => $this->email,

            'phone' => $this->phone,

            'website' => $this->website,

            'registration_number' => $this->registration_number,

            'tax_number' => $this->tax_number,

            'description' => $this->description,

            'fulfillment_region' => $this->fulfillment_region,

            'typical_lead_time' => $this->typical_lead_time,

            'commission_rate' => $this->commission_rate !== null
                ? (float) $this->commission_rate
                : null,

            'effective_commission_rate' => $this->effective_commission_rate,

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
