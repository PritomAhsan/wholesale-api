<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\ProductStatus;
use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Product\ProductRequest;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Product\ProductListResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProductController extends ApiController
{
    /**
     * Product Service
     */
    public function __construct(
        protected ProductService $service
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Private Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Apply Search
     */
    protected function applySearch(
        Builder $query,
        ?string $search
    ): Builder {

        if (! $search) {
            return $query;
        }

        return $query->where(function ($builder) use ($search) {

            $builder

                ->where('name', 'LIKE', "%{$search}%")

                ->orWhere('sku', 'LIKE', "%{$search}%")

                ->orWhere('slug', 'LIKE', "%{$search}%");

        });
    }

    /**
     * Apply Status Filter
     */
    protected function applyStatus(
        Builder $query,
        ?string $status
    ): Builder {

        if (! $status) {
            return $query;
        }

        return $query->where(
            'status',
            $status
        );
    }

    /**
     * Apply Supplier Filter
     */
    protected function applySupplier(
        Builder $query,
        ?int $supplier
    ): Builder {

        if (! $supplier) {
            return $query;
        }

        return $query->where(
            'supplier_id',
            $supplier
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Supplier Ownership
    |--------------------------------------------------------------------------
    |
    | A user with ONLY the Supplier role (not also Admin/Super Admin)
    | must never see or modify another supplier's products — these
    | endpoints are shared between admin staff and suppliers via the
    | same role:Super Admin|Admin|Supplier middleware, so the scoping
    | has to happen here.
    */

    /**
     * True if the current user is a supplier-only account (no admin
     * roles), meaning access must be scoped to their own data.
     */
    protected function isSupplierOnly(Request $request): bool
    {
        $user = $request->user();

        return $user
            && $user->hasRole('Supplier')
            && ! $user->hasAnyRole(['Super Admin', 'Admin']);
    }

    /**
     * The authenticated supplier's own supplier_id, or abort with 403
     * if this user has the Supplier role but no approved supplier
     * record (shouldn't normally happen, but fail closed rather than
     * exposing/scoping to nothing).
     */
    protected function ownSupplierId(Request $request): int
    {
        $supplier = $request->user()->supplier;

        abort_if(
            ! $supplier,
            403,
            'No supplier account is associated with this user.'
        );

        return $supplier->id;
    }

    /**
     * Restrict a product list query to the current supplier's own
     * products when the user is supplier-only. No-op for admin staff.
     */
    protected function scopeToOwnSupplier(
        Builder $query,
        Request $request
    ): Builder {

        if (! $this->isSupplierOnly($request)) {
            return $query;
        }

        return $query->where(
            'supplier_id',
            $this->ownSupplierId($request)
        );
    }

    /**
     * Abort with 403 if this is a supplier-only user trying to touch
     * a product that isn't theirs. No-op for admin staff.
     */
    protected function authorizeProductAccess(
        Product $product,
        Request $request
    ): void {

        if (! $this->isSupplierOnly($request)) {
            return;
        }

        abort_unless(
            $product->supplier_id === $this->ownSupplierId($request),
            403,
            'You do not have access to this product.'
        );

    }

    /**
     * Force supplier_id to the current user's own supplier on
     * create/update, regardless of what was submitted — a supplier
     * must never be able to create or reassign a product to another
     * supplier's name.
     */
    protected function enforceOwnSupplierId(
        array $data,
        Request $request
    ): array {

        if ($this->isSupplierOnly($request)) {
            $data['supplier_id'] = $this->ownSupplierId($request);
        }

        return $data;
    }

    /**
     * Apply Brand Filter
     */
    protected function applyBrand(
        Builder $query,
        ?int $brand
    ): Builder {

        if (! $brand) {
            return $query;
        }

        return $query->where(
            'brand_id',
            $brand
        );
    }

    /**
     * Featured Filter
     */
    protected function applyFeatured(
        Builder $query,
        $featured
    ): Builder {

        if ($featured === null) {
            return $query;
        }

        return $query->where(
            'featured',
            filter_var(
                $featured,
                FILTER_VALIDATE_BOOLEAN
            )
        );
    }

    /**
     * Stock Filter
     */
    protected function applyStock(
        Builder $query,
        ?string $stock
    ): Builder {

        if (! $stock) {
            return $query;
        }

        return match ($stock) {

            'in_stock' =>

            $query->where(
                'stock_quantity',
                '>',
                0
            ),

            'out_of_stock' =>

            $query->where(
                'stock_quantity',
                0
            ),

            default =>

            $query

        };
    }

    /**
     * Price Filter
     */
    protected function applyPrice(
        Builder $query,
        ?float $min,
        ?float $max
    ): Builder {

        if ($min !== null) {

            $query->where(
                'selling_price',
                '>=',
                $min
            );

        }

        if ($max !== null) {

            $query->where(
                'selling_price',
                '<=',
                $max
            );

        }

        return $query;
    }

    /**
     * Sorting
     */
    protected function applySorting(
        Builder $query,
        Request $request
    ): Builder {

        $sortBy = $request->get(
            'sort_by',
            'created_at'
        );

        $direction = $request->get(
            'sort_direction',
            'desc'
        );

        $allowed = [

            'name',

            'selling_price',

            'stock_quantity',

            'created_at',

            'updated_at',

        ];

        if (! in_array($sortBy, $allowed)) {

            $sortBy = 'created_at';

        }

        if (! in_array(
            strtolower($direction),
            ['asc', 'desc']
        )) {

            $direction = 'desc';

        }

        return $query->orderBy(
            $sortBy,
            $direction
        );
    }

    /**
     * Base Query
     */
    protected function baseQuery(): Builder
    {
        return Product::query()

            ->with([

                'supplier',

                'brand',

                'unit',

                'categories',

                'approver',

                'assignedAttributes.attribute',

                'assignedAttributes.value',

                'images',

                'variants',

            ])

            ->withCount([

                'variants',

                'approvals',

            ])

            ->withSum(
                'variants',
                'stock_quantity'
            );
    }

        /*
    |--------------------------------------------------------------------------
    | Product Listing
    |--------------------------------------------------------------------------
    */

    /**
     * Display a listing of products.
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $query = $this->baseQuery();

            $query = $this->scopeToOwnSupplier(
                $query,
                $request
            );

            /*
            |--------------------------------------------------------------------------
            | Search
            |--------------------------------------------------------------------------
            */

            $query = $this->applySearch(
                $query,
                $request->string('search')->toString()
            );

            /*
            |--------------------------------------------------------------------------
            | Filters
            |--------------------------------------------------------------------------
            */

            $query = $this->applyStatus(
                $query,
                $request->get('status')
            );

            $query = $this->applySupplier(
                $query,
                $request->integer('supplier_id')
            );

            $query = $this->applyBrand(
                $query,
                $request->integer('brand_id')
            );

            $query = $this->applyFeatured(
                $query,
                $request->get('featured')
            );

            $query = $this->applyStock(
                $query,
                $request->get('stock')
            );

            $query = $this->applyPrice(
                $query,
                $request->has('min_price')
                    ? (float) $request->min_price
                    : null,

                $request->has('max_price')
                    ? (float) $request->max_price
                    : null
            );

            /*
            |--------------------------------------------------------------------------
            | Sorting
            |--------------------------------------------------------------------------
            */

            $query = $this->applySorting(
                $query,
                $request
            );

            /*
            |--------------------------------------------------------------------------
            | Pagination
            |--------------------------------------------------------------------------
            */

            $perPage = (int) $request->get(
                'per_page',
                15
            );

            if ($perPage < 1) {

                $perPage = 15;

            }

            if ($perPage > 100) {

                $perPage = 100;

            }

            $products = $query->paginate($perPage);

            /*
            |--------------------------------------------------------------------------
            | Response
            |--------------------------------------------------------------------------
            */

            return $this->success([

                'products' => ProductListResource::collection(
                    $products->items()
                ),

                'pagination' => [

                    'current_page' => $products->currentPage(),

                    'last_page' => $products->lastPage(),

                    'per_page' => $products->perPage(),

                    'total' => $products->total(),

                    'from' => $products->firstItem(),

                    'to' => $products->lastItem(),

                    'has_more_pages' => $products->hasMorePages(),

                ],

                'filters' => [

                    'search' => $request->search,

                    'status' => $request->status,

                    'supplier_id' => $request->supplier_id,

                    'brand_id' => $request->brand_id,

                    'featured' => $request->featured,

                    'stock' => $request->stock,

                    'min_price' => $request->min_price,

                    'max_price' => $request->max_price,

                    'sort_by' => $request->get(
                        'sort_by',
                        'created_at'
                    ),

                    'sort_direction' => $request->get(
                        'sort_direction',
                        'desc'
                    ),

                ],

            ], 'Products fetched successfully.');

        } catch (Throwable $exception) {

            report($exception);

            return $this->error(

                'Failed to fetch products.',

                500

            );

        }
    }

        /*
    |--------------------------------------------------------------------------
    | Store Product
    |--------------------------------------------------------------------------
    */

    /**
     * Store a newly created product.
     */
    public function store(
        ProductRequest $request
    ): JsonResponse {

        DB::beginTransaction();

        try {

            $data = $this->enforceOwnSupplierId(
                $request->validated(),
                $request
            );

            $product = $this->service->create(

                $data

            );

            DB::commit();

            return $this->success([

                'product' => new ProductResource(

                    $product->load([

                        'supplier',

                        'brand',

                        'unit',

                        'categories',

                        'approver',

                        'assignedAttributes.attribute',

                        'assignedAttributes.value',

                        'images',
    'variants.attributeValues.attribute',
    'variants.attributeValues.value',
    'variants.images',

                    ])

                ),

            ], 'Product created successfully.', 201);

        } catch (Throwable $exception) {

            DB::rollBack();

            report($exception);

            return $this->error(

                'Failed to create product.',

                500

            );

        }

    }

    /*
    |--------------------------------------------------------------------------
    | Show Product
    |--------------------------------------------------------------------------
    */

    /**
     * Display the specified product.
     */
    public function show(
        Request $request,
        Product $product
    ): JsonResponse {

        try {

            $this->authorizeProductAccess($product, $request);

            $product->load([

                'supplier',

                'brand',

                'unit',

                'variants',

                'categories',

                'approver',

                'assignedAttributes.attribute',

                'assignedAttributes.value',

                'images',
    'variants.attributeValues.attribute',
    'variants.attributeValues.value',
    'variants.images',

            ]);

            return $this->success([

                'product' => new ProductResource(

                    $product

                ),

            ], 'Product detail fetched successfully .');

        } catch (Throwable $exception) {

            report($exception);

            return $this->error(

                'Failed to fetch product.',

                500

            );

        }

    }

    /*
    |--------------------------------------------------------------------------
    | Update Product
    |--------------------------------------------------------------------------
    */

    /**
     * Update the specified product.
     */
    public function update(
        ProductRequest $request,
        Product $product
    ): JsonResponse {

        DB::beginTransaction();

        try {

            $this->authorizeProductAccess($product, $request);

            $data = $this->enforceOwnSupplierId(
                $request->validated(),
                $request
            );

            $product = $this->service->update(

                $product,

                $data

            );

            DB::commit();

            return $this->success([

                'product' => new ProductResource(

                    $product->load([

                        'supplier',

                        'brand',

                        'unit',

                        'categories',

                        'approver',

                        'assignedAttributes.attribute',

                        'assignedAttributes.value',

                        'images',
    'variants.attributeValues.attribute',
    'variants.attributeValues.value',
    'variants.images',

                    ])

                ),

            ], 'Product updated successfully.');

        } catch (Throwable $exception) {

            DB::rollBack();

            report($exception);

            return $this->error(

                'Failed to update product.',

                500

            );

        }

    }

        /*
    |--------------------------------------------------------------------------
    | Delete Product (Soft Delete)
    |--------------------------------------------------------------------------
    */

    /**
     * Soft delete a product.
     */
    public function destroy(
        Request $request,
        Product $product
    ): JsonResponse {

        DB::beginTransaction();

        try {

            $this->authorizeProductAccess($product, $request);

            $this->service->delete($product);

            DB::commit();

            return $this->success(

                [],

                'Product deleted successfully.'

            );

        } catch (Throwable $exception) {

            DB::rollBack();

            report($exception);

            return $this->error(

                'Failed to delete product.',

                500

            );

        }

    }

    /*
    |--------------------------------------------------------------------------
    | Restore Product
    |--------------------------------------------------------------------------
    */

    /**
     * Restore a soft deleted product.
     */
    public function restore(
        string $uuid
    ): JsonResponse {

        DB::beginTransaction();

        try {

            $product = $this->service->restore($uuid);

            DB::commit();

            return $this->success([

                'product' => new ProductResource(

                    $product->load([

                        'supplier',

                        'brand',

                        'unit',

                        'categories',

                        'approver',

                        'assignedAttributes.attribute',

                        'assignedAttributes.value',

                        'images',
    'variants.attributeValues.attribute',
    'variants.attributeValues.value',
    'variants.images',

                    ])

                ),

            ], 'Product restored successfully.');

        } catch (Throwable $exception) {

            DB::rollBack();

            report($exception);

            return $this->error(

                'Failed to restore product.',

                500

            );

        }

    }

    /*
    |--------------------------------------------------------------------------
    | Force Delete Product
    |--------------------------------------------------------------------------
    */

    /**
     * Permanently delete a product.
     */
    public function forceDelete(
        string $uuid
    ): JsonResponse {

        DB::beginTransaction();

        try {

            $this->service->forceDelete($uuid);

            DB::commit();

            return $this->success(

                [],

                'Product permanently deleted.'

            );

        } catch (Throwable $exception) {

            DB::rollBack();

            report($exception);

            return $this->error(

                'Failed to permanently delete product.',

                500

            );

        }

    }

        /*
    |--------------------------------------------------------------------------
    | Product Workflow
    |--------------------------------------------------------------------------
    */

    /**
     * Approve Product
     */
    public function approve(
        Product $product
    ): JsonResponse {

        DB::beginTransaction();

        try {

            $product = $this->service->approve(
                $product,
                auth()->id()
            );

            DB::commit();

            return $this->success([

                'product' => new ProductResource(
                    $product->load([
                        'supplier',
                        'brand',
                        'unit',
                        'categories',
                        'approver',
                        'assignedAttributes.attribute',
                        'assignedAttributes.value',
                        'images',
    'variants.attributeValues.attribute',
    'variants.attributeValues.value',
    'variants.images',
                    ])
                ),

            ], 'Product approved successfully.');

        } catch (Throwable $exception) {

            DB::rollBack();

            report($exception);

            return $this->error(
                'Failed to approve product.',
                500
            );

        }

    }

    /**
     * Reject Product
     */
    public function reject(
        Product $product
    ): JsonResponse {

        DB::beginTransaction();

        try {

            $product = $this->service->reject($product);

            DB::commit();

            return $this->success([

                'product' => new ProductResource(
                    $product
                ),

            ], 'Product rejected successfully.');

        } catch (Throwable $exception) {

            DB::rollBack();

            report($exception);

            return $this->error(
                'Failed to reject product.',
                500
            );

        }

    }

    /**
     * Publish Product
     */
    public function publish(
        Product $product
    ): JsonResponse {

        DB::beginTransaction();

        try {

            $product = $this->service->publish($product);

            DB::commit();

            return $this->success([

                'product' => new ProductResource(
                    $product
                ),

            ], 'Product published successfully.');

        } catch (Throwable $exception) {

            DB::rollBack();

            report($exception);

            return $this->error(
                'Failed to publish product.',
                500
            );

        }

    }

    /**
     * Archive Product
     */
    public function archive(
        Product $product
    ): JsonResponse {

        DB::beginTransaction();

        try {

            $product = $this->service->archive($product);

            DB::commit();

            return $this->success([

                'product' => new ProductResource(
                    $product
                ),

            ], 'Product archived successfully.');

        } catch (Throwable $exception) {

            DB::rollBack();

            report($exception);

            return $this->error(
                'Failed to archive product.',
                500
            );

        }

    }

    /**
     * Toggle Featured
     */
    public function toggleFeatured(
        Product $product
    ): JsonResponse {

        DB::beginTransaction();

        try {

            $product = $this->service->toggleFeatured(
                $product
            );

            DB::commit();

            return $this->success([

                'product' => new ProductResource(
                    $product
                ),

            ], 'Featured status updated successfully.');

        } catch (Throwable $exception) {

            DB::rollBack();

            report($exception);

            return $this->error(
                'Failed to update featured status.',
                500
            );

        }

    }

    /**
     * Update Product Stock
     */
    public function updateStock(
        Request $request,
        Product $product
    ): JsonResponse {

        $request->validate([

            'stock_quantity' => [
                'required',
                'integer',
                'min:0',
            ],

        ]);

        DB::beginTransaction();

        try {

            $product = $this->service->updateStock(

                $product,

                (int) $request->stock_quantity

            );

            DB::commit();

            return $this->success([

                'product' => new ProductResource(
                    $product
                ),

            ], 'Stock updated successfully.');

        } catch (Throwable $exception) {

            DB::rollBack();

            report($exception);

            return $this->error(
                'Failed to update stock.',
                500
            );

        }

    }

}
