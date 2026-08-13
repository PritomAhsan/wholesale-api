<?php

namespace App\Http\Controllers\Api\V1\Product;

use App\Enums\ProductStatus;
use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Product\ProductListResource;
use App\Http\Resources\Product\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class PublicProductController extends ApiController
{
    /**
     * Product Listing
     */
    public function index(Request $request)
    {
        $products = Product::query()
            ->where('status', ProductStatus::PUBLISHED)
            ->with(['supplier', 'brand', 'categories', 'images'])
            ->withCount('variants')
            ->withSum('variants', 'stock_quantity')
            ->when(
                $request->filled('search'),
                fn ($query) => $query->where(
                    'name',
                    'like',
                    '%' . $request->search . '%'
                )
            )
            ->when(
                $request->filled('category'),
                fn ($query) => $query->whereHas(
                    'categories',
                    fn ($q) => $q->where('slug', $request->category)
                )
            )
            ->when(
                $request->filled('brand'),
                fn ($query) => $query->whereHas(
                    'brand',
                    fn ($q) => $q->where('slug', $request->brand)
                )
            )
            ->when(
                $request->filled('supplier'),
                fn ($query) => $query->whereHas(
                    'supplier',
                    fn ($q) => $q->where('uuid', $request->supplier)
                )
            )
            ->when(
                $request->filled('featured'),
                fn ($query) => $query->where('featured', $request->boolean('featured'))
            )
            ->when(
                $request->filled('min_price'),
                fn ($query) => $query->where('selling_price', '>=', $request->float('min_price'))
            )
            ->when(
                $request->filled('max_price'),
                fn ($query) => $query->where('selling_price', '<=', $request->float('max_price'))
            )
            ->orderBy(
                match ($request->get('sort')) {
                    'price_asc' => 'selling_price',
                    'price_desc' => 'selling_price',
                    'newest' => 'created_at',
                    default => 'created_at',
                },
                $request->get('sort') === 'price_desc' ? 'desc' : (
                    $request->get('sort') === 'price_asc' ? 'asc' : 'desc'
                )
            )
            ->paginate(
                $request->integer('per_page', 20)
            );

        return $this->success([
            'products' => ProductListResource::collection($products),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    /**
     * Product Details
     */
    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        if ($product->status !== ProductStatus::PUBLISHED) {

            abort(404);

        }

        $product->load([
            'supplier',
            'brand',
            'unit',
            'categories',
            'assignedAttributes.attribute',
            'assignedAttributes.value',
            'images',
            'variants.attributeValues.attribute',
            'variants.attributeValues.value',
            'variants.images',
        ]);

        return $this->success([
            'product' => new ProductResource($product),
        ]);
    }
}
