<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductApprovalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'uuid' => $this->uuid,

            'action' => $this->action,

            'decision' => $this->decision,

            'status_before' => $this->status_before,

            'status_after' => $this->status_after,

            'remarks' => $this->remarks,

            'reviewer' => $this->reviewer?->name,

            'reviewed_at' => $this->reviewed_at,

            'created_at' => $this->created_at,

        ];
    }
}
