<?php

namespace App\Http\Resources\Unit;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'uuid' => $this->uuid,

            'name' => $this->name,

            'code' => $this->code,

            'symbol' => $this->symbol,

            'description' => $this->description,

            'sort_order' => $this->sort_order,

            'status' => $this->status,

            // 'products_count' => $this->products()->count(),

            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,

        ];
    }
}
