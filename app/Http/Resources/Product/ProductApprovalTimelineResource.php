<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductApprovalTimelineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'uuid' => $this->uuid,

            'action' => $this->action,

            'decision' => $this->decision,

            'status_before' => $this->status_before,

            'status_after' => $this->status_after,

            'reviewer' => [

                'id' => $this->reviewer?->uuid,

                'name' => $this->reviewer?->name,

            ],

            'remarks' => $this->remarks,

            'reviewed_at' => $this->reviewed_at,

        ];
    }
}
