<?php

namespace App\Http\Resources\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventorySummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'variant_uuid' => $this->uuid,

            'sku' => $this->sku,

            'product' => $this->product?->name,

            'stock_quantity' => $this->stock_quantity,

            'low_stock_quantity' => $this->low_stock_quantity,

            'cost_price' => $this->cost_price,

            'inventory_value' => $this->stock_quantity * $this->cost_price,

        ];
    }
}
