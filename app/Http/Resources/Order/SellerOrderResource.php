<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'uuid' => $this->uuid,

            'seller_order_number' => $this->seller_order_number,

            // Buyers only ever see an anonymized seller label. Admin
            // requests, and a supplier viewing their *own* seller order,
            // additionally get the real company name. A supplier's role
            // alone isn't enough — a supplier account is also a buyer
            // account, so without an ownership check a seller could see
            // another seller's identity on their own purchases.
            'supplier' => $this->whenLoaded('supplier', function () {

                $data = [
                    'uuid' => $this->supplier->uuid,
                    'display_name' => $this->supplier->display_name,
                ];

                $viewer = auth('sanctum')->user();

                $isAdmin = $viewer?->hasAnyRole(['Super Admin', 'Admin']);

                $isOwningSupplier = $viewer?->supplier
                    && $viewer->supplier->id === $this->supplier_id;

                if ($isAdmin || $isOwningSupplier) {

                    $data['company_name'] = $this->supplier->company_name;

                }

                return $data;

            }),

            'status' => $this->status,

            'subtotal' => $this->subtotal,

            'tracking_number' => $this->tracking_number,

            'shipping_carrier' => $this->shipping_carrier,

            'shipped_at' => $this->shipped_at,

            'delivered_at' => $this->delivered_at,

            // Whoever is fulfilling this seller order — admin or the
            // supplier themselves — needs the ship-to address. The
            // buyer already gets this from the parent OrderResource,
            // so it isn't gated here.
            'order' => $this->whenLoaded('order', function () {
                return [
                    'order_number' => $this->order->order_number,
                    'placed_at' => $this->order->placed_at,
                    'shipping' => [
                        'name' => $this->order->shipping_name,
                        'phone' => $this->order->shipping_phone,
                        'address' => $this->order->shipping_address,
                        'city' => $this->order->shipping_city,
                        'country' => $this->order->shipping_country,
                        'postal_code' => $this->order->shipping_postal_code,
                    ],
                ];
            }),

            'items' => OrderItemResource::collection(
                $this->whenLoaded('items')
            ),

        ];
    }
}
