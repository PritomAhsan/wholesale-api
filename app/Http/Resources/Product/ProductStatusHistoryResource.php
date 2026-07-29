<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductStatusHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'uuid' => $this->uuid,

            'action' => $this->action,

            'status_before' => $this->status_before,

            'status_after' => $this->status_after,

            'remarks' => $this->remarks,

            'performed_by' => [

                'uuid' => $this->user?->uuid,

                'name' => $this->user?->name,

            ],

            'performed_at' => $this->performed_at,

        ];
    }
}
