<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\ProductVariant\ProductVariantRequest;
use App\Http\Resources\ProductVariant\ProductVariantResource;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Product\ProductVariantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ProductVariantController extends ApiController
{
    public function __construct(
        protected ProductVariantService $service
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Ensure the variant belongs to the given product.
     */
    protected function ensureOwnership(
        Product $product,
        ProductVariant $variant
    ): void {

        abort_unless(

            $variant->product_id === $product->id,

            404,

            'Variant not found.'

        );

    }

    /**
     * Base query for a product's variants.
     */
    protected function baseQuery(
        Product $product
    )
    {
        return $product->variants()

            ->with([

                'attributeValues.attribute',

                'attributeValues.value',

            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    /**
     * Display all variants of a product.
     */
    public function index(
        Request $request,
        Product $product
    ): JsonResponse {

        try {

            $perPage = max(
                1,
                min(
                    (int) $request->get('per_page', 15),
                    100
                )
            );

            $variants = $this->baseQuery($product)

                ->latest()

                ->paginate($perPage);

            return $this->success([

                'variants' => ProductVariantResource::collection(
                    $variants->items()
                ),

                'pagination' => [

                    'current_page' => $variants->currentPage(),

                    'last_page' => $variants->lastPage(),

                    'per_page' => $variants->perPage(),

                    'total' => $variants->total(),

                    'from' => $variants->firstItem(),

                    'to' => $variants->lastItem(),

                ],

            ], 'Variants fetched successfully.');

        } catch (Throwable $exception) {

            report($exception);

            return $this->error(

                'Failed to fetch variants.',

                500

            );

        }

    }

    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    /**
     * Display a single variant.
     */
    public function show(
        Product $product,
        ProductVariant $variant
    ): JsonResponse {

        try {

            $this->ensureOwnership(
                $product,
                $variant
            );

            $variant->load([

                'product',

                'attributeValues.attribute',

                'attributeValues.value',

            ]);

            return $this->success([

                'variant' => new ProductVariantResource(
                    $variant
                ),

            ], 'Variant details fetched successfully.');

        } catch (Throwable $exception) {

            report($exception);

            return $this->error(

                'Failed to fetch variant.',

                500

            );

        }

    }

        /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    /**
     * Store a newly created variant.
     */
    public function store(
        ProductVariantRequest $request,
        Product $product
    ): JsonResponse {

        try {

            $variant = $this->service->create(

                $product,

                $request->validated()

            );

            return $this->success([

                'variant' => new ProductVariantResource(

                    $variant

                ),

            ], 'Variant created successfully.', 201);

        } catch (Throwable $exception) {

            report($exception);

            return $this->error(

                $exception->getMessage(),

                500

            );

        }

    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    /**
     * Update the specified variant.
     */
    public function update(
        ProductVariantRequest $request,
        Product $product,
        ProductVariant $variant
    ): JsonResponse {

        try {

            $this->ensureOwnership(

                $product,

                $variant

            );

            $variant = $this->service->update(

                $variant,

                $request->validated()

            );

            return $this->success([

                'variant' => new ProductVariantResource(

                    $variant

                ),

            ], 'Variant updated successfully.');

        } catch (Throwable $exception) {

            report($exception);

            return $this->error(

                $exception->getMessage(),

                500

            );

        }

    }

    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */

    /**
     * Soft delete a variant.
     */
    public function destroy(
        Product $product,
        ProductVariant $variant
    ): JsonResponse {

        try {

            $this->ensureOwnership(

                $product,

                $variant

            );

            $this->service->delete(

                $variant

            );

            return $this->success(

                [],

                'Variant deleted successfully.'

            );

        } catch (Throwable $exception) {

            report($exception);

            return $this->error(

                $exception->getMessage(),

                500

            );

        }

    }

    /*
    |--------------------------------------------------------------------------
    | Restore
    |--------------------------------------------------------------------------
    */

    /**
     * Restore a soft deleted variant.
     */
    public function restore(
        Product $product,
        string $uuid
    ): JsonResponse {

        try {

            $variant = ProductVariant::withTrashed()

                ->where('uuid', $uuid)

                ->where('product_id', $product->id)

                ->firstOrFail();

            $variant = $this->service->restore(

                $variant->uuid

            );

            return $this->success([

                'variant' => new ProductVariantResource(

                    $variant

                ),

            ], 'Variant restored successfully.');

        } catch (Throwable $exception) {

            report($exception);

            return $this->error(

                $exception->getMessage(),

                500

            );

        }

    }

    /*
    |--------------------------------------------------------------------------
    | Force Delete
    |--------------------------------------------------------------------------
    */

    /**
     * Permanently delete a variant.
     */
    public function forceDelete(
        Product $product,
        string $uuid
    ): JsonResponse {

        try {

            $variant = ProductVariant::withTrashed()

                ->where('uuid', $uuid)

                ->where('product_id', $product->id)

                ->firstOrFail();

            $this->service->forceDelete(

                $variant->uuid

            );

            return $this->success(

                [],

                'Variant permanently deleted.'

            );

        } catch (Throwable $exception) {

            report($exception);

            return $this->error(

                $exception->getMessage(),

                500

            );

        }

    }
}
