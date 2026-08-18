<?php

namespace App\Http\Resources\Supplier;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayoutResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'uuid' => $this->uuid,

            'amount' => (float) $this->amount,

            'status' => $this->status,

            'requested_at' => $this->requested_at,

            'paid_at' => $this->paid_at,

            'reference_note' => $this->reference_note,

            // Only present when explicitly eager-loaded by the admin
            // controller — a supplier viewing their own payouts has no
            // need for (and shouldn't receive) this block.
            'supplier' => $this->whenLoaded('supplier', function () {

                return [
                    'seller_id' => $this->supplier->seller_id,
                    'company_name' => $this->supplier->company_name,
                ];

            }),

            'seller_orders_count' => $this->whenCounted('sellerOrders'),

        ];
    }
}
