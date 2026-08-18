<?php

namespace App\Http\Resources\Dispute;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DisputeImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'image_url' => asset('storage/' . $this->image),

            'uploaded_by_role' => $this->uploader?->hasAnyRole(['Super Admin', 'Admin']) ? 'admin' : 'buyer',

            'created_at' => $this->created_at,

        ];
    }
}
