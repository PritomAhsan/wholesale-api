<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'uuid' => $this->uuid,

            'order_number' => $this->order_number,

            'status' => $this->status,

            'cancellation_reason' => $this->cancellation_reason,

            'cancelled_at' => $this->cancelled_at,

            'can_cancel' => $this->isCancellable(),

            'subtotal' => $this->subtotal,

            'total' => $this->total,

            'currency' => $this->currency,

            'shipping' => [
                'name' => $this->shipping_name,
                'phone' => $this->shipping_phone,
                'address' => $this->shipping_address,
                'city' => $this->shipping_city,
                'country' => $this->shipping_country,
                'postal_code' => $this->shipping_postal_code,
                'cost' => $this->shipping_cost,
                'carrier' => $this->shipping_carrier,
                'service' => $this->shipping_service,
            ],

            'notes' => $this->notes,

            'placed_at' => $this->placed_at,

            // Buyer identity is only useful to admin managing the order —
            // the buyer-facing endpoints already know who they are.
            'buyer' => $this->when(
                $this->relationLoaded('user') &&
                    auth('sanctum')->user()?->hasAnyRole(['Super Admin', 'Admin']),
                fn () => [
                    'uuid' => $this->user->uuid,
                    'full_name' => $this->user->full_name,
                    'email' => $this->user->email,
                    'phone' => $this->user->phone,
                ]
            ),

            'seller_orders' => SellerOrderResource::collection(
                $this->whenLoaded('sellerOrders')
            ),

        ];
    }
}
