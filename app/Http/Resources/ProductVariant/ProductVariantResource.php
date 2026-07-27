<?php

namespace App\Http\Resources\ProductVariant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'uuid' => $this->uuid,

            'product_id' => $this->product_id,

            'sku' => $this->sku,

            'barcode' => $this->barcode,

            'cost_price' => $this->cost_price,

            'selling_price' => $this->selling_price,

            'compare_at_price' => $this->compare_at_price,

            'stock_quantity' => $this->stock_quantity,

            'low_stock_quantity' => $this->low_stock_quantity,

            'weight' => $this->weight,

            'length' => $this->length,

            'width' => $this->width,

            'height' => $this->height,

            'is_active' => $this->is_active,

            'attributes' => $this->whenLoaded(
                'attributeValues',
                function () {

                    return $this->attributeValues->map(function ($item) {

                        return [

                            'attribute' => [

                                'id' => $item->attribute->id,

                                'name' => $item->attribute->name,

                            ],

                            'value' => [

                                'id' => $item->value->id,

                                'value' => $item->value->value,

                            ],

                        ];

                    });

                }
            ),

            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,

            'is_default' => $this->is_default,

            'sort_order' => $this->sort_order,

            'availability' => $this->availability,

            'wholesale_price' => $this->wholesale_price,

            'minimum_order_quantity' => $this->minimum_order_quantity,

            'maximum_order_quantity' => $this->maximum_order_quantity,

            'margin_amount' => $this->margin_amount,

            'margin_percentage' => $this->margin_percentage,

        ];
    }
}
