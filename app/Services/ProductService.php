<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductService
{
    /**
     * Create Product
     */
    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data) {

            /*
            |--------------------------------------------------------------------------
            | Generate Slug & SKU
            |--------------------------------------------------------------------------
            */

            $data['slug'] ??= Str::slug($data['name']);

            $data['sku'] ??= strtoupper(
                'PRD-' . Str::random(8)
            );

            /*
            |--------------------------------------------------------------------------
            | Create Product
            |--------------------------------------------------------------------------
            */

            $product = Product::create($data);

            /*
            |--------------------------------------------------------------------------
            | Categories
            |--------------------------------------------------------------------------
            */

            if (!empty($data['category_ids'])) {

                $product
                    ->categories()
                    ->sync($data['category_ids']);
            }

            /*
            |--------------------------------------------------------------------------
            | Attributes
            |--------------------------------------------------------------------------
            */

            if (!empty($data['attributes'])) {

                foreach ($data['attributes'] as $attribute) {

                    $product
                        ->assignedAttributes()
                        ->create([

                            'attribute_id' =>
                                $attribute['attribute_id'],

                            'attribute_value_id' =>
                                $attribute['attribute_value_id'],

                        ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Return Fresh Model
            |--------------------------------------------------------------------------
            */

            return $product->fresh([

                'supplier',

                'brand',

                'unit',

                'categories',

                'assignedAttributes.attribute',

                'assignedAttributes.value',

            ]);
        });
    }

    /**
     * Update Product
     */
    public function update(
        Product $product,
        array $data
    ): Product {
        return DB::transaction(function () use (
            $product,
            $data
        ) {

            /*
            |--------------------------------------------------------------------------
            | Slug
            |--------------------------------------------------------------------------
            */

            if (
                !empty($data['name']) &&
                empty($data['slug'])
            ) {
                $data['slug'] = Str::slug(
                    $data['name']
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Update Product
            |--------------------------------------------------------------------------
            */

            $product->update($data);

            /*
            |--------------------------------------------------------------------------
            | Categories
            |--------------------------------------------------------------------------
            */

            if (
                array_key_exists(
                    'category_ids',
                    $data
                )
            ) {
                $product
                    ->categories()
                    ->sync(
                        $data['category_ids']
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | Attributes
            |--------------------------------------------------------------------------
            */

            $product
                ->assignedAttributes()
                ->delete();

            if (!empty($data['attributes'])) {

                foreach (
                    $data['attributes']
                    as $attribute
                ) {

                    $product
                        ->assignedAttributes()
                        ->create([

                            'attribute_id' =>
                                $attribute['attribute_id'],

                            'attribute_value_id' =>
                                $attribute['attribute_value_id'],

                        ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Return Fresh Model
            |--------------------------------------------------------------------------
            */

            return $product->fresh([

                'supplier',

                'brand',

                'unit',

                'categories',

                'assignedAttributes.attribute',

                'assignedAttributes.value',

            ]);
        });
    }

    /**
     * Soft Delete
     */
    public function delete(
        Product $product
    ): void {
        $product->delete();
    }

    /**
     * Restore
     */
    public function restore(
        string $uuid
    ): Product {
        $product = Product::withTrashed()
            ->where('uuid', $uuid)
            ->firstOrFail();

        $product->restore();

        return $product;
    }

    /**
     * Force Delete
     */
    public function forceDelete(
        string $uuid
    ): void {
        Product::withTrashed()
            ->where('uuid', $uuid)
            ->firstOrFail()
            ->forceDelete();
    }

    /**
     * Publish
     */
    public function publish(
        Product $product
    ): Product {
        $product->update([

            'status' => 'published',

            'published_at' => now(),

        ]);

        return $product->fresh();
    }

    /**
     * Approve
     */
    public function approve(
        Product $product,
        int $userId
    ): Product {
        $product->update([

            'status' => 'approved',

            'approved_at' => now(),

            'approved_by' => $userId,

        ]);

        return $product->fresh();
    }

    /**
     * Reject
     */
    public function reject(
        Product $product
    ): Product {
        $product->update([

            'status' => 'rejected',

        ]);

        return $product->fresh();
    }

    /**
     * Archive
     */
    public function archive(
        Product $product
    ): Product {
        $product->update([

            'status' => 'archived',

        ]);

        return $product->fresh();
    }

    /**
     * Toggle Featured
     */
    public function toggleFeatured(
        Product $product
    ): Product {
        $product->update([

            'featured' => !$product->featured,

        ]);

        return $product->fresh();
    }

    /**
     * Update Stock
     */
    public function updateStock(
        Product $product,
        int $quantity
    ): Product {
        $product->update([

            'stock_quantity' => $quantity,

        ]);

        return $product->fresh();
    }
}
