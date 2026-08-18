<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\InventoryTransactionType;
use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Controllers\Concerns\ScopesToOwnSupplier;
use App\Http\Requests\Inventory\AdjustInventoryRequest;
use App\Http\Requests\Inventory\DecreaseInventoryRequest;
use App\Http\Requests\Inventory\IncreaseInventoryRequest;
use App\Http\Resources\Inventory\InventoryTransactionResource;
use App\Http\Resources\Inventory\ProductVariantInventoryResource;
use App\Models\ProductVariant;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\JsonResponse;

class ProductVariantInventoryController extends ApiController
{
    use ScopesToOwnSupplier;

    public function __construct(
        protected InventoryService $inventoryService
    ) {
    }

    /**
     * Resolve the variant explicitly by id — this route is NOT under
     * a {product} prefix, and the route parameter is named
     * {variant_id} (not {variant}) specifically to avoid a global
     * Route::bind('variant', ...) in AppServiceProvider that looks up
     * ProductVariant by uuid for any parameter literally named
     * "variant", which would 404 given the numeric id this route
     * actually receives. Not scoped to any product, so ownership is
     * checked against the variant's own parent product below.
     */
    protected function resolveVariant(int|string $variant_id): ProductVariant
    {
        $variant = ProductVariant::with('product')->findOrFail($variant_id);

        $this->authorizeProductAccess($variant->product);

        return $variant;
    }

    public function show(int|string $variant_id): JsonResponse
    {
        $variant = $this->resolveVariant($variant_id);

        return $this->success([
            'inventory' => new ProductVariantInventoryResource($variant),
        ]);
    }

    public function increase(IncreaseInventoryRequest $request, int|string $variant_id): JsonResponse
    {
        $variant = $this->resolveVariant($variant_id);

        $variant = $this->inventoryService->increase(
            $variant,
            $request->integer('quantity'),
            InventoryTransactionType::from($request->transaction_type),
            null,
            $request->remarks
        );

        return $this->success([
            'inventory' => new ProductVariantInventoryResource($variant),
        ], 'Inventory increased successfully.');
    }

    public function decrease(DecreaseInventoryRequest $request, int|string $variant_id): JsonResponse
    {
        $variant = $this->resolveVariant($variant_id);

        $variant = $this->inventoryService->decrease(
            $variant,
            $request->integer('quantity'),
            InventoryTransactionType::from($request->transaction_type),
            null,
            $request->remarks
        );

        return $this->success([
            'inventory' => new ProductVariantInventoryResource($variant),
        ], 'Inventory decreased successfully.');
    }

    public function adjust(AdjustInventoryRequest $request, int|string $variant_id): JsonResponse
    {
        $variant = $this->resolveVariant($variant_id);

        $variant = $this->inventoryService->adjust(
            $variant,
            $request->integer('stock_quantity'),
            $request->remarks
        );

        return $this->success([
            'inventory' => new ProductVariantInventoryResource($variant),
        ], 'Inventory adjusted successfully.');
    }

    public function history(int|string $variant_id): JsonResponse
    {
        $variant = $this->resolveVariant($variant_id);

        return $this->success([
            'transactions' => InventoryTransactionResource::collection(
                $variant->inventoryTransactions()->paginate(20)
            ),
        ]);
    }
}
