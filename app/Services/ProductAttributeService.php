<?php

namespace App\Services;

use App\Models\Attribute;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductAttributeService
{
    public function create(array $data): Attribute
    {
        return DB::transaction(function () use ($data) {

            return Attribute::create([

                'uuid' => (string) Str::uuid(),

                'category_id' => $data['category_id'] ?? null,

                'name' => $data['name'],

                'slug' => Str::slug($data['name']),

                'type' => $data['type'],

                'is_filterable' => $data['is_filterable'] ?? false,

                'is_required' => $data['is_required'] ?? false,

                'status' => $data['status'] ?? true,

                'sort_order' => $data['sort_order'] ?? 0,

            ]);

        });
    }

    public function update(
        Attribute $attribute,
        array $data
    ): Attribute {

        return DB::transaction(function () use ($attribute, $data) {

            $attribute->update([

                'category_id' => $data['category_id'] ?? null,

                'name' => $data['name'],

                'slug' => Str::slug($data['name']),

                'type' => $data['type'],

                'is_filterable' => $data['is_filterable'] ?? false,

                'is_required' => $data['is_required'] ?? false,

                'status' => $data['status'] ?? true,

                'sort_order' => $data['sort_order'] ?? 0,

            ]);

            return $attribute->fresh();

        });
    }

    public function delete(Attribute $attribute): void
    {
        $attribute->delete();
    }

    public function restore(string $uuid): Attribute
    {
        $attribute = Attribute::withTrashed()
            ->where('uuid', $uuid)
            ->firstOrFail();

        $attribute->restore();

        return $attribute;
    }

    public function forceDelete(string $uuid): void
    {
        Attribute::withTrashed()
            ->where('uuid', $uuid)
            ->firstOrFail()
            ->forceDelete();
    }

    public function toggleStatus(
        Attribute $attribute
    ): Attribute {

        $attribute->update([
            'status' => ! $attribute->status,
        ]);

        return $attribute->fresh();
    }
}
