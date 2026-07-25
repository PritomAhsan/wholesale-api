<?php

namespace App\Http\Controllers\Api\V1\Product;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Brand\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Request;

class PublicBrandController extends ApiController
{
    /**
     * Brand Listing
     */
    public function index(Request $request)
    {
        $brands = Brand::query()
            ->where('status', true)
            ->when(
                $request->filled('search'),
                fn ($query) => $query->where(
                    'name',
                    'like',
                    '%' . $request->search . '%'
                )
            )
            ->when(
                $request->boolean('featured'),
                fn ($query) => $query->where(
                    'featured',
                    true
                )
            )
            // ->withCount('products')
            ->orderBy('name')
            ->paginate(
                $request->integer('per_page', 20)
            );

        return $this->success([
            'brands' => BrandResource::collection($brands),
            'pagination' => [
                'current_page' => $brands->currentPage(),
                'last_page' => $brands->lastPage(),
                'per_page' => $brands->perPage(),
                'total' => $brands->total(),
            ],
        ]);
    }

    /**
     * Brand Details
     */
    public function show(Brand $brand)
    {
        if (! $brand->status) {

            abort(404);

        }

        $brand->loadCount('products');

        return $this->success([
            'brand' => new BrandResource($brand),
        ]);
    }

    /**
     * Featured Brands
     */
    public function featured()
    {
        $brands = Brand::query()
            ->where('status', true)
            ->where('featured', true)
            ->withCount('products')
            ->orderBy('name')
            ->get();

        return $this->success([
            'brands' => BrandResource::collection($brands),
        ]);
    }
}
