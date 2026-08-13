<?php

namespace App\Http\Resources\Rfq;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RfqResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'uuid' => $this->uuid,

            'product_name' => $this->product_name,

            'preferred_supplier_name' => $this->preferred_supplier_name,

            // Returned to the submitting buyer — anonymized so the RFQ
            // response can't be used to identify and contact a seller
            // directly, bypassing the platform.
            'supplier' => $this->whenLoaded('supplier', function () {

                return [
                    'uuid' => $this->supplier->uuid,
                    'display_name' => $this->supplier->display_name,
                ];

            }),

            'quantity' => $this->quantity,

            'unit' => $this->unit,

            'budget' => $this->budget,

            'destination_country' => $this->destination_country,

            'required_delivery_date' => $this->required_delivery_date,

            'message' => $this->message,

            'attachment_url' => $this->attachment_path
                ? asset('storage/' . $this->attachment_path)
                : null,

            'contact_name' => $this->contact_name,

            'contact_email' => $this->contact_email,

            'contact_phone' => $this->contact_phone,

            'status' => $this->status,

            'admin_response' => $this->admin_response,

            'responded_at' => $this->responded_at,

            'created_at' => $this->created_at,

        ];
    }
}
