<?php

namespace App\Http\Controllers\Api\V1\Supplier;

use App\Enums\ProductStatus;
use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Product\ProductListResource;
use App\Http\Resources\Supplier\SupplierPublicResource;
use App\Models\Supplier;
use Illuminate\Http\Request;

class PublicSupplierController extends ApiController
{
    /**
     * A small, bounded preview of approved sellers for homepage display —
     * not a browsable directory. No search, filter or pagination beyond
     * a fixed limit, so buyers still can't shop the marketplace by seller.
     */
    public function featured(Request $request)
    {
        $suppliers = Supplier::where('status', 'approved')
            ->withCount(['products' => fn ($query) => $query->where('status', ProductStatus::PUBLISHED)])
            ->having('products_count', '>', 0)
            ->inRandomOrder()
            ->limit($request->integer('limit', 3))
            ->get()
            ->each(fn ($supplier) => $supplier->load([
                'products' => fn ($query) => $query
                    ->where('status', ProductStatus::PUBLISHED)
                    ->with('categories'),
            ]));

        return $this->success([
            'sellers' => SupplierPublicResource::collection($suppliers),
        ]);
    }

    /**
     * Protected seller profile. Looked up by the public Seller ID —
     * never by the internal uuid or company slug, so the URL itself
     * never exposes anything beyond the anonymized identifier.
     */
    public function show(string $sellerId, Request $request)
    {
        $supplier = Supplier::where('seller_id', $sellerId)
            ->where('status', 'approved')
            ->firstOrFail();

        $supplier->load([
            'products' => fn ($query) => $query
                ->where('status', ProductStatus::PUBLISHED)
                ->with('categories'),
        ]);

        $listings = Supplier::where('id', $supplier->id)
            ->firstOrFail()
            ->products()
            ->where('status', ProductStatus::PUBLISHED)
            ->with(['supplier', 'brand', 'categories', 'images'])
            ->withCount('variants')
            ->withSum('variants', 'stock_quantity')
            ->when(
                $request->filled('brand'),
                fn ($query) => $query->whereHas(
                    'brand',
                    fn ($q) => $q->where('slug', $request->brand)
                )
            )
            ->when(
                $request->filled('min_price'),
                fn ($query) => $query->where('selling_price', '>=', $request->float('min_price'))
            )
            ->when(
                $request->filled('max_price'),
                fn ($query) => $query->where('selling_price', '<=', $request->float('max_price'))
            )
            ->when(
                $request->filled('min_moq'),
                fn ($query) => $query->where('min_order_quantity', '>=', $request->float('min_moq'))
            )
            ->when(
                $request->filled('max_moq'),
                fn ($query) => $query->where('min_order_quantity', '<=', $request->float('max_moq'))
            )
            ->when(
                $request->boolean('in_stock'),
                fn ($query) => $query->where('stock_quantity', '>', 0)
            )
            ->orderBy(
                match ($request->get('sort')) {
                    'price_asc', 'price_desc' => 'selling_price',
                    default => 'created_at',
                },
                $request->get('sort') === 'price_asc' ? 'asc' : 'desc'
            )
            ->paginate($request->integer('per_page', 12));

        return $this->success([
            'seller' => new SupplierPublicResource($supplier),
            'listings' => ProductListResource::collection($listings),
            'pagination' => [
                'current_page' => $listings->currentPage(),
                'last_page' => $listings->lastPage(),
                'per_page' => $listings->perPage(),
                'total' => $listings->total(),
            ],
        ]);
    }
}
