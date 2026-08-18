<?php

namespace App\Http\Resources\Supplier;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Public seller profile. Only approved, verified-safe attributes are
 * exposed here — legal business name, contact details, email, phone
 * and website never reach this resource so buyers can't identify or
 * route around the platform to order directly from a seller.
 */
class SupplierPublicResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'seller_id' => $this->seller_id,

            'business_type' => $this->business_type,

            'fulfillment_region' => $this->fulfillment_region,

            'typical_lead_time' => $this->typical_lead_time,

            'verified' => $this->status === 'approved',

            'member_since' => $this->approved_at ?? $this->created_at,

            'logo' => $this->logo
                ? asset('storage/' . $this->logo)
                : null,

            'banner' => $this->banner
                ? asset('storage/' . $this->banner)
                : null,

            // Controller pre-loads `products` scoped to published listings
            // only, so both fields below reflect live, buyer-visible
            // inventory rather than draft or archived products.
            'categories' => $this->whenLoaded('products', function () {

                return $this->products
                    ->flatMap(fn ($product) => $product->categories)
                    ->unique('id')
                    ->values()
                    ->map(fn ($category) => [
                        'uuid' => $category->uuid,
                        'name' => $category->name,
                        'slug' => $category->slug,
                    ]);

            }),

            'listings_count' => $this->whenLoaded(
                'products',
                fn () => $this->products->count()
            ),

        ];
    }
}
