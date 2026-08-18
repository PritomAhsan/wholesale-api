<?php

namespace App\Http\Resources\Support;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'uuid' => $this->uuid,

            'topic' => $this->topic,

            'name' => $this->name,

            'business_email' => $this->business_email,

            'account_email' => $this->account_email,

            'reference_number' => $this->reference_number,

            'message' => $this->message,

            'attachment_url' => $this->attachment_path
                ? asset('storage/' . $this->attachment_path)
                : null,

            'status' => $this->status,

            'created_at' => $this->created_at,

        ];
    }
}
