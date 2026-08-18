<?php

namespace App\Http\Resources\Dispute;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DisputeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'uuid' => $this->uuid,

            'reason' => $this->reason,

            'description' => $this->description,

            'status' => $this->status,

            'resolution' => $this->resolution,

            'resolution_amount' => $this->resolution_amount !== null
                ? (float) $this->resolution_amount
                : null,

            'resolution_note' => $this->resolution_note,

            'resolved_at' => $this->resolved_at,

            'created_at' => $this->created_at,

            'seller_order' => $this->whenLoaded('sellerOrder', function () {

                return [
                    'uuid' => $this->sellerOrder->uuid,
                    'seller_order_number' => $this->sellerOrder->seller_order_number,
                    'subtotal' => (float) $this->sellerOrder->subtotal,
                    'payable_amount' => $this->sellerOrder->payable_amount !== null
                        ? (float) $this->sellerOrder->payable_amount
                        : null,
                    'paid_out' => $this->sellerOrder->payout_id !== null,

                    'delivered_at' => $this->sellerOrder->delivered_at,
                    'seller_id' => $this->sellerOrder->supplier?->seller_id,
                ];

            }),

            // Buyer identity — first name + last initial only, same
            // anonymization discipline used for reviews.
            'buyer' => $this->whenLoaded('user', function () {

                $first = $this->user->first_name;
                $lastInitial = $this->user->last_name
                    ? strtoupper(substr($this->user->last_name, 0, 1)) . '.'
                    : '';

                return trim($first . ' ' . $lastInitial);

            }),

            'resolved_by' => $this->whenLoaded('resolver', fn () => $this->resolver?->full_name),

            'images' => DisputeImageResource::collection($this->whenLoaded('images')),

        ];
    }
}
