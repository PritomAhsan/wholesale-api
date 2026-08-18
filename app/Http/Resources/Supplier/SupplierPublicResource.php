<?php

namespace App\Http\Resources\Supplier;

use App\Models\StoreFollow;
use App\Services\Supplier\SellerVerificationScoreService;
use App\Support\MediaUrl;
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
        $trust = app(SellerVerificationScoreService::class)->scoreFor($this->resource);

        return [

            'seller_id' => $this->seller_id,

            'business_type' => $this->business_type,

            'fulfillment_region' => $this->fulfillment_region,

            'typical_lead_time' => $this->typical_lead_time,

            'verified' => $this->status === 'approved',

            'member_since' => $this->approved_at ?? $this->created_at,

            'logo' => MediaUrl::resolve($this->logo),

            'banner' => MediaUrl::resolve($this->banner),

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

            'completed_order_count' => $trust['completed_order_count'],

            'store_rating' => $trust['avg_rating'] !== null
                ? round($trust['avg_rating'], 1)
                : null,

            'reviews_count' => $trust['review_count'],

            'verification_score' => $trust['score'],

            'badges' => $trust['badges'],

            // Resolved via the sanctum guard directly rather than
            // $request->user() — this resource is also rendered from
            // the public /sellers/{sellerId} route, which has no
            // auth:sanctum middleware (a logged-out buyer must still
            // see the profile), so nothing upstream has attempted
            // token resolution yet.
            'is_followed' => auth('sanctum')->user()
                ? StoreFollow::where('user_id', auth('sanctum')->id())
                    ->where('supplier_id', $this->id)
                    ->exists()
                : null,

        ];
    }
}
