<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\InventoryTransactionType;
use App\Http\Controllers\Api\V1\ApiController;
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
    public function __construct(
        protected InventoryService $inventoryService
    ) {
    }

    public function show(ProductVariant $variant): JsonResponse
    {
        return $this->success([
            'inventory' => new ProductVariantInventoryResource($variant),
        ]);
    }

    public function increase(IncreaseInventoryRequest $request, ProductVariant $variant): JsonResponse
    {
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

    public function decrease(DecreaseInventoryRequest $request, ProductVariant $variant): JsonResponse
    {
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

    public function adjust(AdjustInventoryRequest $request, ProductVariant $variant): JsonResponse
    {
        $variant = $this->inventoryService->adjust(
            $variant,
            $request->integer('stock_quantity'),
            $request->remarks
        );

        return $this->success([
            'inventory' => new ProductVariantInventoryResource($variant),
        ], 'Inventory adjusted successfully.');
    }

    public function history(ProductVariant $variant): JsonResponse
    {
        return $this->success([
            'transactions' => InventoryTransactionResource::collection(
                $variant->inventoryTransactions()->paginate(20)
            ),
        ]);
    }
}
