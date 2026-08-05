<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\ProductAttribute\ProductAttributeValueRequest;
use App\Http\Resources\ProductAttribute\ProductAttributeValueResource;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Services\ProductAttributeValueService;
use Illuminate\Http\Request;

class ProductAttributeValueController extends ApiController
{
    public function __construct(
        protected ProductAttributeValueService $service
    ) {
    }

    /**
     * Display all values of a Product Attribute.
     */
    public function index(
        Request $request,
        Attribute $productAttribute
    ) {
        $values = $productAttribute
            ->values()

            ->when(
                $request->filled('search'),
                fn ($query) => $query->where(
                    'value',
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

            ->orderBy('sort_order')

            ->paginate(
                $request->integer('per_page', 15)
            );

        return $this->success([

            'attribute' => [

                'uuid' => $productAttribute->uuid,

                'name' => $productAttribute->name,

                'type' => $productAttribute->type,

            ],

            'values' => ProductAttributeValueResource::collection($values),

            'pagination' => [

                'current_page' => $values->currentPage(),

                'last_page' => $values->lastPage(),

                'per_page' => $values->perPage(),

                'total' => $values->total(),

            ],

        ]);
    }

    /**
     * Store a new Product Attribute Value.
     */
    public function store(
        ProductAttributeValueRequest $request,
        Attribute $productAttribute
    ) {

        $value = $this->service->create(

            $productAttribute,

            $request->validated()

        );

        return $this->success([

            'value' => new ProductAttributeValueResource(
                $value->load('attribute')
            )

        ], 'Attribute value created successfully.', 201);
    }

    /**
     * Display a single Product Attribute Value.
     */
    public function show(
        AttributeValue $productAttributeValue
    ) {

        return $this->success([

            'value' => new ProductAttributeValueResource(

                $productAttributeValue->load('attribute')

            )

        ]);
    }

    /**
     * Update Product Attribute Value.
     */
    public function update(
        ProductAttributeValueRequest $request,
        AttributeValue $productAttributeValue
    ) {

        $value = $this->service->update(

            $productAttributeValue,

            $request->validated()

        );

        return $this->success([

            'value' => new ProductAttributeValueResource(

                $value->load('attribute')

            )

        ], 'Attribute value updated successfully.');
    }

    /**
     * Delete Product Attribute Value.
     */
    public function destroy(
        AttributeValue $productAttributeValue
    ) {

        $this->service->delete($productAttributeValue);

        return $this->success(

            null,

            'Attribute value deleted successfully.'

        );
    }

    /**
     * Restore deleted Product Attribute Value.
     */
    public function restore(string $uuid)
    {
        $value = $this->service->restore($uuid);

        return $this->success([

            'value' => new ProductAttributeValueResource(

                $value->load('attribute')

            )

        ], 'Attribute value restored successfully.');
    }

    /**
     * Permanently delete Product Attribute Value.
     */
    public function forceDelete(string $uuid)
    {
        $this->service->forceDelete($uuid);

        return $this->success(

            null,

            'Attribute value permanently deleted.'

        );
    }

    /**
     * Toggle Status.
     */
    public function toggleStatus(
        AttributeValue $productAttributeValue
    ) {

        $value = $this->service->toggleStatus(
            $productAttributeValue
        );

        return $this->success([

            'value' => new ProductAttributeValueResource(

                $value->load('attribute')

            )

        ]);
    }
}
