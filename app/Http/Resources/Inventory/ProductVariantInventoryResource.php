<?php

namespace App\Http\Resources\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantInventoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'variant_uuid' => $this->uuid,

            'sku' => $this->sku,

            'stock_quantity' => $this->stock_quantity,

            'low_stock_quantity' => $this->low_stock_quantity,

            'availability' => $this->availability,

        ];
    }
}
