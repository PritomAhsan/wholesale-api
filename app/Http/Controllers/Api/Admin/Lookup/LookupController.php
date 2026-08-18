<?php

namespace App\Http\Controllers\Api\Admin\Lookup;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class LookupController extends Controller
{
    /**
     * Categories
     */
    public function categories(): JsonResponse
    {
        return $this->lookup(

            Category::query(),

            [
                'id',
                'uuid',
                'name',
                'slug',
            ]

        );
    }

    /**
     * Brands
     */
    public function brands(): JsonResponse
    {
        return $this->lookup(

            Brand::query(),

            [
                'id',
                'uuid',
                'name',
            ]

        );
    }

    /**
     * Units
     */
    public function units(): JsonResponse
    {
        return $this->lookup(

            Unit::query(),

            [
                'id',
                'uuid',
                'name',
                'symbol',
            ]

        );
    }

    /**
     * Suppliers
     */
    public function suppliers(): JsonResponse
    {
        return $this->lookup(

            Supplier::query(),

            [
                'id',
                'uuid',
                'company_name',
            ]

        );
    }

    /**
     * Published products, for admin selects (e.g. deal authoring).
     */
    public function products(): JsonResponse
    {
        return $this->lookup(

            Product::query()->where('status', 'published'),

            [
                'id',
                'uuid',
                'name',
            ]

        );
    }

    /**
     * Attribute lookup.
     */
    public function attributes(): JsonResponse
    {
        return $this->lookup(
            Attribute::query(),

                [
                    'id',
                    'name',
                ],
            // 'Attributes fetched successfully.'
        );
    }

    /**
     * Attribute value lookup.
     */
    public function attributeValues(): JsonResponse
    {
        return $this->lookup(
            AttributeValue::query(),
            [
                'id',
                'uuid',
                'attribute_id',
                'value',
            ]
        );
    }

    /**
     * Generic Lookup
     */
    private function lookup(
        Builder $query,
        array $columns
    ): JsonResponse {

        return response()->json([

            'success' => true,

            'data' => $query
                ->orderBy(
                    $columns[2] ?? 'id'
                )
                ->get($columns),

        ]);
    }
}
