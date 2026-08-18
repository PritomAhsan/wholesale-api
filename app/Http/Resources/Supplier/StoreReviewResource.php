<?php

namespace App\Http\Resources\Supplier;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'uuid' => $this->uuid,

            'rating' => $this->rating,

            'communication_rating' => $this->communication_rating,

            'shipping_rating' => $this->shipping_rating,

            'packaging_rating' => $this->packaging_rating,

            'comment' => $this->comment,

            // First name + last initial only — same identity discipline
            // as product reviews and protected Seller IDs.
            'author' => $this->whenLoaded('user', function () {

                $first = $this->user->first_name;
                $lastInitial = $this->user->last_name
                    ? strtoupper(substr($this->user->last_name, 0, 1)) . '.'
                    : '';

                return trim($first . ' ' . $lastInitial);

            }),

            'verified_purchase' => true,

            'created_at' => $this->created_at,

        ];
    }
}
