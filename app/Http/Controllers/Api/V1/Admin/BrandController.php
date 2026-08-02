<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Brand\BrandRequest;
use App\Http\Resources\Brand\BrandResource;
use App\Models\Brand;
use App\Services\BrandService;
use Illuminate\Http\Request;

class BrandController extends ApiController
{
    public function __construct(
        protected BrandService $brandService
    ) {
    }

    /**
     * Display a listing of brands.
     */
    public function index(Request $request)
    {
        $brands = Brand::query()
            ->when(
                $request->filled('search'),
                fn ($query) => $query->where(
                    'name',
                    'like',
                    '%' . $request->search . '%'
                )
            )
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where(
                    'status',
                    $request->boolean('status')
                )
            )
            ->when(
                $request->filled('featured'),
                fn ($query) => $query->where(
                    'featured',
                    $request->boolean('featured')
                )
            )
            ->withCount('products')
            ->orderByDesc('id')
            ->paginate(
                $request->integer('per_page', 15)
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
     * Store a newly created brand.
     */
    public function store(BrandRequest $request)
    {
        $brand = $this->brandService->create(
            $request->validated()
        );

        return $this->success([
            'brand' => new BrandResource($brand),
        ], 'Brand created successfully.', 201);
    }

    /**
     * Display the specified brand.
     */
    public function show(Brand $brand)
    {
        $brand->loadCount('products');

        return $this->success([
            'brand' => new BrandResource($brand),
        ]);
    }

    /**
     * Update the specified brand.
     */
    public function update(
        BrandRequest $request,
        Brand $brand
    ) {
        $brand = $this->brandService->update(
            $brand,
            $request->validated()
        );

        return $this->success([
            // 'brand' => new BrandResource(
            //     $brand->loadCount('products')
            // ),
        ], 'Brand updated successfully.');
    }

    /**
     * Remove the specified brand.
     */
    public function destroy(Brand $brand)
    {
        if ($brand->products()->exists()) {

            return $this->error(
                'This brand has products and cannot be deleted.',
                [],
                422
            );

        }

        $this->brandService->delete($brand);

        return $this->success(
            null,
            'Brand deleted successfully.'
        );
    }

    /**
     * Restore Brand
     */
    public function restore(string $uuid)
    {
        $brand = $this->brandService->restore($uuid);

        return $this->success([
            'brand' => new BrandResource($brand),
        ], 'Brand restored successfully.');
    }

    /**
     * Permanently Delete Brand
     */
    public function forceDelete(string $uuid)
    {
        $this->brandService->forceDelete($uuid);

        return $this->success(
            null,
            'Brand permanently deleted.'
        );
    }

    /**
     * Toggle Featured
     */
    public function toggleFeatured(
        Brand $brand
    ) {
        $brand = $this->brandService->toggleFeatured(
            $brand
        );

        return $this->success([
            'brand' => new BrandResource($brand),
        ]);
    }

    /**
     * Toggle Status
     */
    public function toggleStatus(
        Brand $brand
    ) {
        $brand = $this->brandService->toggleStatus(
            $brand
        );

        return $this->success([
            'brand' => new BrandResource($brand),
        ]);
    }
}
