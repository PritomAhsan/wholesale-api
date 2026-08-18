<?php

namespace App\Http\Resources\Review;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'uuid' => $this->uuid,

            'rating' => $this->rating,

            'comment' => $this->comment,

            // First name + last initial only — matches the same identity
            // discipline used everywhere else (protected Seller IDs,
            // anonymized buyer-facing supplier names).
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
