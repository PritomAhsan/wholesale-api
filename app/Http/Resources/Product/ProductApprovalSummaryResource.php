<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductApprovalSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'product_uuid' => $this->uuid,

            'product_name' => $this->name,

            'status' => $this->status,

            'supplier' => [

                'id' => $this->supplier?->uuid,

                'company_name' => $this->supplier?->company_name,

            ],

            'latest_approval' => new ProductApprovalTimelineResource(
                $this->whenLoaded('latestApproval')
            ),

            'approval_count' => $this->approvals_count,

        ];
    }
}
