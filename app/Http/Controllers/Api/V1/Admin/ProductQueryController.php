<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Product\ProductListRequest;
use App\Http\Resources\Product\ProductListResource;
use App\Services\Product\ProductQueryService;
use Illuminate\Http\JsonResponse;

class ProductQueryController extends ApiController
{
    public function __construct(
        protected ProductQueryService $service
    ) {
    }

    public function index(
        ProductListRequest $request
    ): JsonResponse {

        return $this->success([

            'products' => ProductListResource::collection(

                $this->service->list($request)

            )

        ]);

    }

    public function statistics(): JsonResponse
    {
        return $this->success([

            'statistics' => $this->service->statistics()

        ]);
    }
}
