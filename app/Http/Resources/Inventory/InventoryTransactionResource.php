<?php

namespace App\Http\Resources\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'uuid' => $this->uuid,

            'transaction_type' => $this->transaction_type,

            'movement_type' => $this->movement_type,

            'quantity' => $this->quantity,

            'stock_before' => $this->stock_before,

            'stock_after' => $this->stock_after,

            'remarks' => $this->remarks,

            'created_by' => $this->creator?->name,

            'created_at' => $this->created_at,

        ];
    }
}
