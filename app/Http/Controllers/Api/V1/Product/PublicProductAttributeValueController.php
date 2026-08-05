<?php

namespace App\Http\Controllers\Api\V1\Product;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\ProductAttribute\ProductAttributeValueResource;
use App\Models\Attribute;

class PublicProductAttributeValueController extends ApiController
{
    /**
     * Get all values for an attribute
     */
    public function index(
        Attribute $productAttribute
    ) {

        $values = $productAttribute

            ->values()

            ->where('status', true)

            ->orderBy('sort_order')

            ->get();

        return $this->success([

            'attribute' => [

                'uuid' => $productAttribute->uuid,

                'name' => $productAttribute->name,

                'type' => $productAttribute->type,

            ],

            'values' => ProductAttributeValueResource::collection($values),

        ]);
    }
}
