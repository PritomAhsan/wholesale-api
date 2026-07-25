<?php

namespace App\Services;

use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductAttributeValueService
{
    /**
     * Create Attribute Value
     */
    public function create(
        ProductAttribute $attribute,
        array $data
    ): ProductAttributeValue {

        return DB::transaction(function () use (
            $attribute,
            $data
        ) {

            return ProductAttributeValue::create([

                'uuid' => (string) Str::uuid(),

                'product_attribute_id' => $attribute->id,

                'value' => $data['value'],

                'sort_order' => $data['sort_order'] ?? 0,

                'status' => $data['status'] ?? true,

            ]);

        });
    }

    /**
     * Update Attribute Value
     */
    public function update(
        ProductAttributeValue $value,
        array $data
    ): ProductAttributeValue {

        return DB::transaction(function () use (
            $value,
            $data
        ) {

            $value->update([

                'value' => $data['value'],

                'sort_order' => $data['sort_order'] ?? 0,

                'status' => $data['status'] ?? true,

            ]);

            return $value->fresh();

        });
    }

    /**
     * Soft Delete
     */
    public function delete(
        ProductAttributeValue $value
    ): void {

        $value->delete();

    }

    /**
     * Restore
     */
    public function restore(
        string $uuid
    ): ProductAttributeValue {

        $value = ProductAttributeValue::withTrashed()

            ->where('uuid', $uuid)

            ->firstOrFail();

        $value->restore();

        return $value;
    }

    /**
     * Force Delete
     */
    public function forceDelete(
        string $uuid
    ): void {

        ProductAttributeValue::withTrashed()

            ->where('uuid', $uuid)

            ->firstOrFail()

            ->forceDelete();

    }

    /**
     * Toggle Status
     */
    public function toggleStatus(
        ProductAttributeValue $value
    ): ProductAttributeValue {

        $value->update([

            'status' => ! $value->status,

        ]);

        return $value->fresh();
    }
}
