<?php

namespace App\Http\Controllers\Api\V1\Product;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\ProductAttribute\ProductAttributeResource;
use App\Models\ProductAttribute;

class PublicProductAttributeController extends ApiController
{
    public function index()
    {
        $attributes = ProductAttribute::query()
            ->where('status', true)
            ->with('values')
            ->orderBy('sort_order')
            ->get();

        return $this->success([
            'attributes' => ProductAttributeResource::collection($attributes),
        ]);
    }
}
