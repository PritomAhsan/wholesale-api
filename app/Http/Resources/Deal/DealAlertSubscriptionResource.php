<?php

namespace App\Http\Resources\Deal;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DealAlertSubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'uuid' => $this->uuid,

            'email' => $this->email,

            'subscribed_at' => $this->subscribed_at,

        ];
    }
}
