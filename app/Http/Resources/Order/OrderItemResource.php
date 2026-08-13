<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'uuid' => $this->uuid,

            'product' => $this->product ? [
                'uuid' => $this->product->uuid,
                'slug' => $this->product->slug,
            ] : null,

            'product_name' => $this->product_name,

            'product_sku' => $this->product_sku,

            'product_image' => $this->product_image,

            'unit_price' => $this->unit_price,

            'quantity' => $this->quantity,

            'line_total' => $this->line_total,

        ];
    }
}
