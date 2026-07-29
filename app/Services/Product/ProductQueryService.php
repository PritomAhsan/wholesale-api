<?php

namespace App\Services\Product;

use App\Enums\ProductStatus;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ProductQueryService
{
    /**
     * Allowed sortable columns.
     */
    protected array $sortableColumns = [

        'name' => 'name',

        'price' => 'price',

        'stock' => 'stock',

        'created_at' => 'created_at',

        'updated_at' => 'updated_at',

    ];

    /**
     * Build the base query.
     */
    protected function buildQuery(): Builder
    {
        return Product::query()

            ->with([
                'supplier',
                'brand',
                'categories',
                'variants',
            ])

            ->withCount([
                'variants',
            ]);
    }

    /**
     * Product listing.
     */
    public function list(Request $request)
    {
        $query = $this->buildQuery();

        $this->applySearch(
            $query,
            $request
        );

        $this->applyFilters(
            $query,
            $request
        );

        $this->applySorting(
            $query,
            $request
        );

        return $query->paginate(

            $request->integer(
                'per_page',
                20
            )

        );
    }

    /**
     * Dashboard statistics.
     */
    public function statistics(): array
    {
        return [

            'total' => Product::count(),

            'draft' => Product::whereStatus(ProductStatus::DRAFT)->count(),

            'pending' => Product::whereStatus(ProductStatus::PENDING)->count(),

            'approved' => Product::whereStatus(ProductStatus::APPROVED)->count(),

            'published' => Product::whereStatus(ProductStatus::PUBLISHED)->count(),

            'unpublished' => Product::whereStatus(ProductStatus::UNPUBLISHED)->count(),

            'archived' => Product::whereStatus(ProductStatus::ARCHIVED)->count(),

            'featured' => Product::where('featured', true)->count(),

            'with_inventory' => Product::whereHas('variants', function ($query) {

                $query->where('stock_quantity', '>', 0);

            })->count(),

            'without_inventory' => Product::whereDoesntHave('variants', function ($query) {

                $query->where('stock_quantity', '>', 0);

            })->count(),

        ];
    }

        /**
     * Apply keyword search.
     */
    protected function applySearch(
        Builder $query,
        Request $request
    ): void {

        $search = trim((string) $request->input('search'));

        if ($search === '') {
            return;
        }

        $query->where(function (Builder $builder) use ($search) {

            $builder->where('name', 'like', "%{$search}%")

                ->orWhere('slug', 'like', "%{$search}%")

                ->orWhere('sku', 'like', "%{$search}%")

                ->orWhereHas('supplier', function (Builder $supplier) use ($search) {

                    $supplier->where('company_name', 'like', "%{$search}%");

                })

                ->orWhereHas('brand', function (Builder $brand) use ($search) {

                    $brand->where('name', 'like', "%{$search}%");

                });

        });

    }

        /**
     * Apply request filters.
     */
    protected function applyFilters(
        Builder $query,
        Request $request
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Product Status
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {

            $query->where(
                'status',
                $request->status
            );

        }

        /*
        |--------------------------------------------------------------------------
        | Supplier
        |--------------------------------------------------------------------------
        */

        if ($request->filled('supplier')) {

            $query->whereHas('supplier', function (Builder $supplier) use ($request) {

                $supplier->where(
                    'uuid',
                    $request->supplier
                );

            });

        }

        /*
        |--------------------------------------------------------------------------
        | Brand
        |--------------------------------------------------------------------------
        */

        if ($request->filled('brand')) {

            $query->whereHas('brand', function (Builder $brand) use ($request) {

                $brand->where(
                    'uuid',
                    $request->brand
                );

            });

        }

        /*
        |--------------------------------------------------------------------------
        | Category
        |--------------------------------------------------------------------------
        */

        if ($request->filled('category')) {

            $query->whereHas('categories', function (Builder $category) use ($request) {

                $category->where(
                    'uuid',
                    $request->category
                );

            });

        }

        /*
        |--------------------------------------------------------------------------
        | Featured
        |--------------------------------------------------------------------------
        */

        if ($request->filled('featured')) {

            $query->where(
                'featured',
                $request->boolean('featured')
            );

        }

        /*
        |--------------------------------------------------------------------------
        | Has Inventory
        |--------------------------------------------------------------------------
        */

        if ($request->filled('has_inventory')) {

            if ($request->boolean('has_inventory')) {

                $query->whereHas('variants', function (Builder $variant) {

                    $variant->where(
                        'stock_quantity',
                        '>',
                        0
                    );

                });

            } else {

                $query->whereDoesntHave('variants', function (Builder $variant) {

                    $variant->where(
                        'stock_quantity',
                        '>',
                        0
                    );

                });

            }

        }

        /*
        |--------------------------------------------------------------------------
        | Created Date Range
        |--------------------------------------------------------------------------
        */

        if ($request->filled('created_from')) {

            $query->whereDate(
                'created_at',
                '>=',
                $request->created_from
            );

        }

        if ($request->filled('created_to')) {

            $query->whereDate(
                'created_at',
                '<=',
                $request->created_to
            );

        }

    }

    /**
     * Apply sorting.
     */
    protected function applySorting(
        Builder $query,
        Request $request
    ): void {

        $sort = $request->input(
            'sort',
            'created_at'
        );

        $direction = strtolower(
            $request->input(
                'direction',
                'desc'
            )
        );

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        if (! array_key_exists($sort, $this->sortableColumns)) {

            $sort = 'created_at';

        }

        /*
        |--------------------------------------------------------------------------
        | Stock Sorting
        |--------------------------------------------------------------------------
        */

        if ($sort === 'stock') {

            $query
                ->withSum(
                    'variants as stock_quantity_sum',
                    'stock_quantity'
                )
                ->orderBy(
                    'stock_quantity_sum',
                    $direction
                );

            return;

        }

        $query->orderBy(
            $this->sortableColumns[$sort],
            $direction
        );

    }
}
