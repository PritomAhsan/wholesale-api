<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\ProductAttribute\ProductAttributeRequest;
use App\Http\Resources\ProductAttribute\ProductAttributeResource;
use App\Models\ProductAttribute;
use App\Services\ProductAttributeService;
use Illuminate\Http\Request;

class ProductAttributeController extends ApiController
{
    public function __construct(
        protected ProductAttributeService $service
    ) {}

    public function index(Request $request)
    {
        $attributes = ProductAttribute::query()
            ->with('category')
            ->withCount('values')
            ->when(
                $request->filled('search'),
                fn ($q) => $q->where(
                    'name',
                    'like',
                    '%' . $request->search . '%'
                )
            )
            ->orderBy('sort_order')
            ->paginate(
                $request->integer('per_page', 15)
            );

        return $this->success([
            'attributes' => ProductAttributeResource::collection($attributes),
            'pagination' => [
                'current_page' => $attributes->currentPage(),
                'last_page' => $attributes->lastPage(),
                'per_page' => $attributes->perPage(),
                'total' => $attributes->total(),
            ],
        ]);
    }

    public function store(ProductAttributeRequest $request)
    {
        $attribute = $this->service->create(
            $request->validated()
        );

        return $this->success([
            'attribute' => new ProductAttributeResource($attribute),
        ], 'Product attribute created successfully.', 201);
    }

    public function show(ProductAttribute $productAttribute)
    {
        return $this->success([
            'attribute' => new ProductAttributeResource(
                $productAttribute->load('category')
            ),
        ]);
    }

    public function update(
        ProductAttributeRequest $request,
        ProductAttribute $productAttribute
    ) {
        $attribute = $this->service->update(
            $productAttribute,
            $request->validated()
        );

        return $this->success([
            'attribute' => new ProductAttributeResource($attribute),
        ], 'Product attribute updated successfully.');
    }

    public function destroy(ProductAttribute $productAttribute)
    {
        $this->service->delete($productAttribute);

        return $this->success(
            null,
            'Product attribute deleted successfully.'
        );
    }
}
