<?php

namespace App\Services;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductAttributeValueService
{
    /**
     * Create Attribute Value
     */
    public function create(
        Attribute $attribute,
        array $data
    ): AttributeValue {

        return DB::transaction(function () use (
            $attribute,
            $data
        ) {

            return AttributeValue::create([

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
        AttributeValue $value,
        array $data
    ): AttributeValue {

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
        AttributeValue $value
    ): void {

        $value->delete();

    }

    /**
     * Restore
     */
    public function restore(
        string $uuid
    ): AttributeValue {

        $value = AttributeValue::withTrashed()

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

        AttributeValue::withTrashed()

            ->where('uuid', $uuid)

            ->firstOrFail()

            ->forceDelete();

    }

    /**
     * Toggle Status
     */
    public function toggleStatus(
        AttributeValue $value
    ): AttributeValue {

        $value->update([

            'status' => ! $value->status,

        ]);

        return $value->fresh();
    }
}
