<?php

namespace App\Services\Product;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductVariantService
{
    /**
     * Generate Variant SKU
     */
    protected function generateSku(Product $product): string
    {
        do {

            $sku = strtoupper(
                'VAR-' .
                $product->id .
                '-' .
                Str::random(8)
            );

        } while (
            ProductVariant::where('sku', $sku)->exists()
        );

        return $sku;
    }

    /**
     * Build Variant Combination Key
     *
     * Example:
     *
     * Color=Black
     * Size=XL
     *
     * becomes
     *
     * 1:5|2:10
     */
    protected function buildCombinationKey(
        array $attributes
    ): string {

        $pairs = collect($attributes)

            ->map(function ($attribute) {

                return

                    $attribute['attribute_id']

                    .

                    ':'

                    .

                    $attribute['attribute_value_id'];

            })

            ->sort()

            ->values()

            ->toArray();

        return implode('|', $pairs);
    }

    /**
     * Ensure Variant Combination
     * doesn't already exist.
     */
    protected function ensureUniqueCombination(

        Product $product,

        array $attributes,

        ?ProductVariant $ignore = null

    ): void {

        $key = $this->buildCombinationKey(
            $attributes
        );

        $variants = $product

            ->variants()

            ->with('attributeValues')

            ->get();

        foreach ($variants as $variant) {

            if (

                $ignore &&

                $ignore->id === $variant->id

            ) {

                continue;

            }

            $existing = $this->buildCombinationKey(

                $variant

                    ->attributeValues

                    ->map(function ($item) {

                        return [

                            'attribute_id'

                                =>

                                $item->attribute_id,

                            'attribute_value_id'

                                =>

                                $item->attribute_value_id,

                        ];

                    })

                    ->toArray()

            );

            if ($existing === $key) {

                throw ValidationException::withMessages([

                    'attributes' => [

                        'This product variant already exists.'

                    ]

                ]);

            }

        }

    }

    /**
     * Sync Variant Attributes
     */
    protected function syncAttributes(

        ProductVariant $variant,

        array $attributes

    ): void {

        $variant

            ->attributeValues()

            ->delete();

        foreach ($attributes as $attribute) {

            $variant

                ->attributeValues()

                ->create([

                    'attribute_id'

                        =>

                        $attribute['attribute_id'],

                    'attribute_value_id'

                        =>

                        $attribute['attribute_value_id'],

                ]);

        }

    }

    /**
     * Load Relationships
     */
    protected function loadVariant(

        ProductVariant $variant

    ): ProductVariant {

        return $variant->load([

            'product',

            'attributeValues.attribute',

            'attributeValues.value',

        ]);

    }

    protected function ensureSingleDefault(
        ProductVariant $variant
    ): void {

        if (! $variant->is_default) {

            return;

        }

        ProductVariant::where('product_id', $variant->product_id)

            ->whereKeyNot($variant->id)

            ->update([

                'is_default' => false,

            ]);

    }

        /*
    |--------------------------------------------------------------------------
    | Create Variant
    |--------------------------------------------------------------------------
    */

    /**
     * Create a product variant.
     */
    public function create(
        Product $product,
        array $data
    ): ProductVariant {

        return DB::transaction(function () use ($product, $data) {

            $this->ensureUniqueCombination(
                $product,
                $data['attributes']
            );

            $variant = ProductVariant::create([

                'product_id' => $product->id,

                'sku' => $data['sku']
                    ?? $this->generateSku($product),

                'barcode' => $data['barcode'] ?? null,

                'cost_price' => $data['cost_price'],

                'selling_price' => $data['selling_price'],

                'compare_at_price' =>
                    $data['compare_at_price'] ?? null,

                'stock_quantity' =>
                    $data['stock_quantity'] ?? 0,

                'low_stock_quantity' =>
                    $data['low_stock_quantity'] ?? 5,

                'weight' =>
                    $data['weight'] ?? null,

                'length' =>
                    $data['length'] ?? null,

                'width' =>
                    $data['width'] ?? null,

                'height' =>
                    $data['height'] ?? null,

                'is_active' =>
                    $data['is_active'] ?? true,

                'wholesale_price' =>
                    $data['wholesale_price'] ?? null,

                'minimum_order_quantity' =>
                    $data['minimum_order_quantity'] ?? 1,

                'maximum_order_quantity' =>
                    $data['maximum_order_quantity'] ?? null,

            ]);

            if ($variant->is_default) {

                $this->ensureSingleDefault($variant);

            }

            $this->syncAttributes(

                $variant,

                $data['attributes']

            );

            return $this->loadVariant($variant);

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Update Variant
    |--------------------------------------------------------------------------
    */

    /**
     * Update a product variant.
     */
    public function update(
        ProductVariant $variant,
        array $data
    ): ProductVariant {

        return DB::transaction(function () use ($variant, $data) {

            $product = $variant->product;

            $this->ensureUniqueCombination(

                $product,

                $data['attributes'],

                $variant

            );

            $variant->update([

                'sku' => $data['sku']
                    ?? $variant->sku,

                'barcode' =>
                    $data['barcode']
                    ?? $variant->barcode,

                'cost_price' =>
                    $data['cost_price'],

                'selling_price' =>
                    $data['selling_price'],

                'compare_at_price' =>
                    $data['compare_at_price'] ?? null,

                'stock_quantity' =>
                    $data['stock_quantity'],

                'low_stock_quantity' =>
                    $data['low_stock_quantity'] ?? 5,

                'weight' =>
                    $data['weight'],

                'length' =>
                    $data['length'],

                'width' =>
                    $data['width'],

                'height' =>
                    $data['height'],

                'is_active' =>
                    $data['is_active'] ?? true,

                'wholesale_price' =>
                    $data['wholesale_price'] ?? null,

                'minimum_order_quantity' =>
                    $data['minimum_order_quantity'] ?? 1,

                'maximum_order_quantity' =>
                    $data['maximum_order_quantity'] ?? null,

            ]);

            if ($variant->is_default) {

                $this->ensureSingleDefault($variant);

            }

            $this->syncAttributes(

                $variant,

                $data['attributes']

            );

            return $this->loadVariant($variant);

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Soft Delete
    |--------------------------------------------------------------------------
    */

    /**
     * Soft delete a variant.
     */
    public function delete(
        ProductVariant $variant
    ): void {

        DB::transaction(function () use ($variant) {

            $variant->delete();

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Restore
    |--------------------------------------------------------------------------
    */

    /**
     * Restore a soft deleted variant.
     */
    public function restore(
        string $uuid
    ): ProductVariant {

        return DB::transaction(function () use ($uuid) {

            $variant = ProductVariant::withTrashed()

                ->where('uuid', $uuid)

                ->firstOrFail();

            $variant->restore();

            return $this->loadVariant($variant);

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Force Delete
    |--------------------------------------------------------------------------
    */

    /**
     * Permanently delete a variant.
     */
    public function forceDelete(
        string $uuid
    ): void {

        DB::transaction(function () use ($uuid) {

            $variant = ProductVariant::withTrashed()

                ->where('uuid', $uuid)

                ->firstOrFail();

            $variant->forceDelete();

        });

    }
}
