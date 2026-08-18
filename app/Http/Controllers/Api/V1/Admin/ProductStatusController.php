<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Controllers\Concerns\ScopesToOwnSupplier;
use App\Http\Requests\ProductStatus\ArchiveProductRequest;
use App\Http\Requests\ProductStatus\PublishProductRequest;
use App\Http\Requests\ProductStatus\RestoreProductRequest;
use App\Http\Requests\ProductStatus\UnpublishProductRequest;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Product\ProductStatusHistoryResource;
use App\Models\Product;
use App\Services\Product\ProductStatusService;
use Illuminate\Http\JsonResponse;

class ProductStatusController extends ApiController
{
    use ScopesToOwnSupplier;

    public function __construct(
        protected ProductStatusService $service
    ) {
    }

    public function publish(
        PublishProductRequest $request,
        Product $product
    ): JsonResponse {

        $product = $this->service->publish(
            $product,
            $request->remarks
        );

        return $this->success([
            'product' => new ProductResource($product),
        ], 'Product published successfully.');
    }

    public function unpublish(
        UnpublishProductRequest $request,
        Product $product
    ): JsonResponse {

        $product = $this->service->unpublish(
            $product,
            $request->remarks
        );

        return $this->success([
            'product' => new ProductResource($product),
        ], 'Product unpublished successfully.');
    }

    public function archive(
        ArchiveProductRequest $request,
        Product $product
    ): JsonResponse {

        $product = $this->service->archive(
            $product,
            $request->remarks
        );

        return $this->success([
            'product' => new ProductResource($product),
        ], 'Product archived successfully.');
    }

    public function restore(
        RestoreProductRequest $request,
        Product $product
    ): JsonResponse {

        $product = $this->service->restore(
            $product,
            $request->remarks
        );

        return $this->success([
            'product' => new ProductResource($product),
        ], 'Product restored successfully.');
    }

    public function history(Product $product): JsonResponse
    {
        $this->authorizeProductAccess($product);

        return $this->success([

            'history' => ProductStatusHistoryResource::collection(

                $product->statusHistory()
                    ->with('user')
                    ->paginate(20)

            )

        ]);
    }
}
