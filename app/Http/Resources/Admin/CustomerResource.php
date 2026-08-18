<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'uuid' => $this->uuid,

            'full_name' => $this->full_name,

            'email' => $this->email,

            'phone' => $this->phone,

            'status' => $this->status,

            'orders_count' => $this->whenCounted('orders'),

            'created_at' => $this->created_at,

        ];
    }
}
