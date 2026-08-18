<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\ProductStatus;
use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Controllers\Concerns\ScopesToOwnSupplier;
use App\Http\Requests\ProductApproval\ApproveProductRequest;
use App\Http\Requests\ProductApproval\RejectProductRequest;
use App\Http\Requests\ProductApproval\SubmitProductForReviewRequest;
use App\Http\Resources\Product\ProductApprovalResource;
use App\Http\Resources\Product\ProductApprovalTimelineResource;
use App\Http\Resources\Product\ProductResource;
use App\Models\Product;
use App\Models\User;
use App\Services\Product\ProductApprovalQueryService;
use App\Services\Product\ProductApprovalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductApprovalController extends ApiController
{
    use ScopesToOwnSupplier;

    protected ProductApprovalQueryService $queryService;
    protected ProductApprovalService $service;

    public function __construct(
        ProductApprovalService $service,
        ProductApprovalQueryService $queryService
    ) {
        $this->service = $service;
        $this->queryService = $queryService;
    }

    public function submit(
        SubmitProductForReviewRequest $request,
        Product $product
    ): JsonResponse {

        $this->authorizeProductAccess($product);

        $product = $this->service->submit(
            $product,
            $request->remarks
        );

        return $this->success([
            'product' => new ProductResource($product),
        ], 'Product submitted for review successfully.');
    }

    public function approve(
        ApproveProductRequest $request,
        Product $product
    ): JsonResponse {

        $product = $this->service->approve(
            $product,
            $request->remarks
        );

        return $this->success([
            'product' => new ProductResource($product),
        ], 'Product approved successfully.');
    }

    public function reject(
        RejectProductRequest $request,
        Product $product
    ): JsonResponse {

        $product = $this->service->reject(
            $product,
            $request->remarks
        );

        return $this->success([
            'product' => new ProductResource($product),
        ], 'Product rejected successfully.');
    }

    public function history(Product $product): JsonResponse
    {
        $this->authorizeProductAccess($product);

        return $this->success([
            'history' => ProductApprovalResource::collection(
                $product->approvals()->paginate(20)
            ),
        ]);
    }

    public function pending(Request $request): JsonResponse
    {
        $products = Product::where('status', ProductStatus::PENDING)

            ->with(['supplier', 'images'])

            ->latest()

            ->paginate(
                $request->integer('per_page', 20)
            );

        return $this->success([
            'products' => ProductResource::collection($products),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function timeline(Product $product)
    {
        $this->authorizeProductAccess($product);

        return $this->success([

            'timeline' => ProductApprovalTimelineResource::collection(

                $this->queryService->timeline($product)

            )

        ]);
    }

    public function statistics()
    {
        return $this->success([

            'statistics' => $this->queryService->statistics()

        ]);
    }

    public function supplierHistory(User $user)
    {
        return $this->success([

            'history' => ProductApprovalTimelineResource::collection(

                $this->queryService->supplierHistory($user)

            )

        ]);
    }
}
