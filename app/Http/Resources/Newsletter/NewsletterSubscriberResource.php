<?php

namespace App\Http\Resources\Newsletter;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsletterSubscriberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'uuid' => $this->uuid,

            'email' => $this->email,

            'topics' => $this->topics,

            'frequency' => $this->frequency,

            'subscribed_at' => $this->subscribed_at,

            'unsubscribed_at' => $this->unsubscribed_at,

            'created_at' => $this->created_at,

        ];
    }
}
