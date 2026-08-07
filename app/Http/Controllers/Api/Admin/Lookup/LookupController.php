<?php

namespace App\Http\Controllers\Api\Admin\Lookup;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Builder;

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
